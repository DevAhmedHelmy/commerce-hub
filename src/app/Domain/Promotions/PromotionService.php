<?php

declare(strict_types=1);

namespace App\Domain\Promotions;

use App\Models\ProductOffer;
use App\Models\ProductUnit;
use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Determines the eligible active offer for a product unit (prompt 41 §11). Validity is evaluated
 * SERVER-SIDE only: active AND now within [starts_at, ends_at]. When several offers overlap, the
 * lowest offer price is chosen (deterministic). This service does not compute the lower-of rule —
 * that lives in {@see \App\Domain\Pricing\PricingService}.
 */
final class PromotionService
{
    public function activeOfferFor(ProductUnit $unit, ?DateTimeInterface $at = null): ?ProductOffer
    {
        $at = $at ?? Carbon::now();

        return $unit->offers()
            ->where('is_active', true)
            ->where('starts_at', '<=', $at)
            ->where('ends_at', '>=', $at)
            ->orderBy('offer_price')
            ->first();
    }
}
