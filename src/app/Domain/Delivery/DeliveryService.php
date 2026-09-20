<?php

declare(strict_types=1);

namespace App\Domain\Delivery;

use App\Domain\Support\Enums\DeliveryDiscountType;
use App\Domain\Support\Money;
use App\Models\DeliveryArea;
use App\Models\DeliveryDiscountRule;
use App\Models\DeliverySlot;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Delivery eligibility + fee calculation (prompt 42 §7, R5/R6). Discounts never stack: among all
 * eligible active rules, the one producing the LARGEST actual saving against the base fee wins;
 * ties break to the higher qualifying `min_subtotal`. The final fee never goes below zero.
 */
final class DeliveryService
{
    public function isAreaOrderable(DeliveryArea $area): bool
    {
        return (bool) $area->is_active;
    }

    /**
     * Compute the delivery fee for an area given the qualifying (effective product) subtotal.
     */
    public function quote(DeliveryArea $area, Money $subtotal, ?DateTimeInterface $at = null): DeliveryQuote
    {
        $baseFee = $area->baseFeeMoney();

        /** @var Collection<int, DeliveryDiscountRule> $rules */
        $rules = DeliveryDiscountRule::query()
            ->where('is_active', true)
            ->where('min_subtotal', '<=', $subtotal->minorUnits)
            ->get();

        $best = null;
        $bestSaving = Money::zero();

        foreach ($rules as $rule) {
            $saving = $this->savingFor($rule, $baseFee);

            if ($best === null
                || $saving->greaterThan($bestSaving)
                || ($saving->equals($bestSaving) && $rule->min_subtotal > $best->min_subtotal)) {
                $best = $rule;
                $bestSaving = $saving;
            }
        }

        if ($best === null || $bestSaving->isZero()) {
            return new DeliveryQuote($baseFee, Money::zero(), $baseFee);
        }

        return new DeliveryQuote(
            baseFee: $baseFee,
            discountAmount: $bestSaving,
            finalFee: $baseFee->minus($bestSaving),
            appliedRuleId: $best->id,
            appliedRuleType: $best->type->value,
        );
    }

    public function isSlotSelectable(DeliverySlot $slot, DateTimeInterface $date): bool
    {
        $date = Carbon::instance(Carbon::parse($date));

        return $slot->is_active
            && (int) $slot->day_of_week === (int) $date->isoWeekday()
            && $date->startOfDay()->greaterThanOrEqualTo(Carbon::now()->startOfDay());
    }

    /**
     * Active slots for the given date's weekday, ordered for display.
     *
     * @return Collection<int, DeliverySlot>
     */
    public function availableSlots(DateTimeInterface $date): Collection
    {
        $weekday = Carbon::instance(Carbon::parse($date))->isoWeekday();

        return DeliverySlot::query()
            ->where('is_active', true)
            ->where('day_of_week', $weekday)
            ->orderBy('sort_order')
            ->get();
    }

    /** Saving is always capped at the base fee (final fee never negative). */
    private function savingFor(DeliveryDiscountRule $rule, Money $baseFee): Money
    {
        return match ($rule->type) {
            DeliveryDiscountType::Fixed => Money::fromMinor((int) $rule->value)->cappedAt($baseFee),
            DeliveryDiscountType::Percentage => $baseFee->percentage((int) $rule->value),
            DeliveryDiscountType::FreeDelivery => $baseFee,
        };
    }
}
