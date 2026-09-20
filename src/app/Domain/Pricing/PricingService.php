<?php

declare(strict_types=1);

namespace App\Domain\Pricing;

use App\Domain\Promotions\PromotionService;
use App\Domain\Support\Money;
use App\Models\ProductPriceTier;
use App\Models\ProductUnit;
use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Authoritative source of a product unit's effective price (prompt 41 §9). Given a sellable unit
 * and a quantity, it evaluates base price, the applicable quantity tier, and any active offer, then
 * applies the LOWER-OF rule (R3) — never stacking. This is the single place pricing is computed;
 * controllers/Blade/Filament/cart must call it rather than re-deriving prices.
 *
 * Sub-unit and primary-unit prices are independent (prompt 37 §7): a unit is priced only by its own
 * base/tier/offer — never derived from the other unit via the conversion factor.
 */
final class PricingService
{
    public function __construct(private readonly PromotionService $promotions)
    {
    }

    /**
     * Effective price for `$quantity` of `$unit` at `$at` (defaults to now). Deterministic:
     * applied = min(base, eligible tier, eligible offer); ties resolve offer → tier → normal.
     */
    public function priceFor(ProductUnit $unit, int $quantity, ?DateTimeInterface $at = null): PriceResult
    {
        $at = $at ?? Carbon::now();
        $quantity = max(1, $quantity);

        $base = $unit->basePriceMoney();

        $tierModel = $this->resolveTier($unit, $quantity);
        $tier = $tierModel?->unitPriceMoney();

        $offerModel = $this->promotions->activeOfferFor($unit, $at);
        $offer = $offerModel?->offerPriceMoney();

        $applied = $base;
        foreach ([$tier, $offer] as $candidate) {
            if ($candidate !== null && $candidate->lessThan($applied)) {
                $applied = $candidate;
            }
        }

        // Ties resolve offer → tier → normal (documented, deterministic).
        if ($offer !== null && $offer->equals($applied)) {
            $source = PriceResult::SOURCE_OFFER;
        } elseif ($tier !== null && $tier->equals($applied)) {
            $source = PriceResult::SOURCE_TIER;
        } else {
            $source = PriceResult::SOURCE_NORMAL;
        }

        return new PriceResult(
            base: $base,
            tier: $tier,
            offer: $offer,
            applied: $applied,
            appliedSource: $source,
            quantity: $quantity,
            lineTotal: $applied->times($quantity),
            unitSaving: $base->minus($applied),
            tierId: $source === PriceResult::SOURCE_TIER ? $tierModel?->id : null,
            offerId: $source === PriceResult::SOURCE_OFFER ? $offerModel?->id : null,
        );
    }

    /**
     * Representative "from" price for listing cards (§44) — the unit's effective price at quantity 1,
     * so quantity tiers don't apply but an active offer still does. Flat-priced units yield base.
     */
    public function baselineFromPrice(ProductUnit $unit, ?DateTimeInterface $at = null): PriceResult
    {
        return $this->priceFor($unit, 1, $at);
    }

    /** Deepest active tier whose threshold is met by the selected-unit quantity (never combined). */
    private function resolveTier(ProductUnit $unit, int $quantity): ?ProductPriceTier
    {
        return $unit->priceTiers()
            ->where('is_active', true)
            ->where('min_quantity', '<=', $quantity)
            ->orderByDesc('min_quantity')
            ->first();
    }
}
