<x-layouts.app :title="$product->localized('name')">
    <x-slot:header>
        <div class="flex items-center gap-3 px-4 py-3">
            <a href="{{ url()->previous() }}" class="text-muted" aria-label="{{ __('messages.actions.back') }}">›</a>
            <span class="truncate text-lg font-bold text-content" dir="auto">{{ $product->localized('name') }}</span>
        </div>
    </x-slot:header>

    @php
        $sellableUnits = $product->units
            ->where('is_sellable', true)->where('is_active', true)
            ->sortBy('sort_order')->values();
        $subStock = $product->subStock();
        $default = $sellableUnits->firstWhere('level', \App\Models\ProductUnit::LEVEL_PRIMARY)
            ?? $sellableUnits->first();
        // Real effective pricing (prompt 41): PricingService is the authoritative source. We pass each
        // unit's base, any active offer, and active tier brackets (all integer minor units) so Alpine
        // re-prices with the exact lower-of rule as the quantity changes — no pricing math is invented here.
        $pricing = app(\App\Domain\Pricing\PricingService::class);
        $unitData = $sellableUnits->map(function ($u) use ($pricing, $subStock) {
            $baseline = $pricing->baselineFromPrice($u);
            return [
                'id' => $u->id,
                'name' => $u->label(),
                'base' => (int) $u->base_price,
                'offer' => $baseline->offer?->minorUnits,
                'tiers' => $u->priceTiers->where('is_active', true)->sortBy('min_quantity')
                    ->map(fn ($t) => ['min' => (int) $t->min_quantity, 'price' => (int) $t->unit_price])->values()->all(),
                'orderable' => $subStock >= (int) $u->conversion_to_sub_unit && $subStock > 0,
            ];
        })->values();
    @endphp

    <div class="flex aspect-square items-center justify-center overflow-hidden rounded-[--radius-md] bg-surface-muted">
        @if ($product->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) }}"
                alt="{{ $product->localized('name') }}" class="h-full w-full object-contain">
        @else
            <span class="text-5xl font-bold text-muted" aria-hidden="true">{{ mb_substr($product->localized('name'), 0, 1) }}</span>
        @endif
    </div>

    @if ($product->brand)
        <p class="mt-4 text-sm text-muted" dir="auto">{{ $product->brand }}</p>
    @endif
    <h1 class="text-xl font-bold text-content" dir="auto">{{ $product->localized('name') }}</h1>
    <div class="mt-2"><x-availability-badge :status="$product->displayAvailability()" /></div>

    @if (filled($product->localized('description')))
        <p class="mt-3 text-sm leading-6 text-muted" dir="auto">{{ $product->localized('description') }}</p>
    @endif

    @if ($sellableUnits->isNotEmpty())
        {{-- Unit + quantity selection foundation (design §5.9/§5.10). Both the primary and
             sub units are sellable and independently priced (prompt 37). Cart submission is
             wired in Phase F; here selection only re-prices the estimate client-side. --}}
        <div x-data="{
                unitId: @js($default?->id),
                qty: 1,
                units: @js($unitData),
                unit() { return this.units.find(u => u.id === this.unitId); },
                fmt(m) {
                    const egp = Math.floor(m / 100), rem = m % 100;
                    const g = egp.toLocaleString('en-US');
                    return rem ? `${g}.${String(rem).padStart(2, '0')} ج` : `${g} ج`;
                },
                effectiveMinor() {
                    const u = this.unit(); if (!u) return 0;
                    let p = u.base;
                    if (u.offer !== null && u.offer < p) p = u.offer;
                    let tier = null;
                    for (const t of u.tiers) { if (this.qty >= t.min) tier = t.price; }
                    if (tier !== null && tier < p) p = tier;
                    return p;
                },
                effective() { return this.fmt(this.effectiveMinor()); },
                hasOffer() { const u = this.unit(); return u && u.offer !== null && u.offer < u.base; },
             }" class="mt-6">
            <p class="text-sm font-semibold text-content">{{ __('catalog.units') }}</p>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($sellableUnits as $u)
                    @php $unitOrderable = $subStock >= (int) $u->conversion_to_sub_unit && $subStock > 0; @endphp
                    <button type="button" @click="unitId = {{ $u->id }}"
                        :class="unitId === {{ $u->id }} ? 'border-primary bg-primary/10 text-content' : 'border-border text-muted'"
                        class="rounded-[--radius-sm] border px-3 py-2 text-sm font-semibold {{ $unitOrderable ? '' : 'opacity-60' }}" dir="auto">
                        {{ $u->label() }}@unless ($unitOrderable) · {{ __('inventory.out_of_stock') }}@endunless
                    </button>
                @endforeach
            </div>

            <div class="mt-4 flex items-center gap-2" dir="ltr">
                <p class="text-xl font-bold text-content" x-text="effective()"></p>
                <template x-if="hasOffer()">
                    <span class="rounded-[--radius-sm] bg-success/10 px-2 py-0.5 text-xs font-semibold text-success">{{ __('catalog.offer') }}</span>
                </template>
            </div>

            <div class="mt-4">
                <p class="mb-1 text-sm font-semibold text-content">{{ __('catalog.quantity') }}</p>
                <div class="inline-flex items-center gap-4 rounded-full border border-border px-3 py-2">
                    <button type="button" @click="qty = Math.max(1, qty - 1)" class="text-xl text-primary" aria-label="-">−</button>
                    <span class="w-8 text-center text-lg font-bold tabular-nums text-content" x-text="qty"></span>
                    <button type="button" @click="qty++" class="text-xl text-primary" aria-label="+">+</button>
                </div>
            </div>

            <template x-if="!units.find(u => u.id === unitId)?.orderable">
                <p class="mt-4 text-sm font-semibold text-muted">{{ __('inventory.out_of_stock') }}</p>
            </template>

            <button type="button" :disabled="!units.find(u => u.id === unitId)?.orderable"
                class="mt-6 w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-base font-semibold text-inverse hover:bg-primary-strong disabled:cursor-not-allowed disabled:opacity-50">
                {{ __('catalog.add_to_cart') }}
            </button>
        </div>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
