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
        $unitData = $sellableUnits->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->label(),
            'price' => \App\Domain\Support\MoneyFormatter::format($u->basePriceMoney()),
            // Orderable if the product's sub-unit stock covers at least one of this unit.
            'orderable' => $subStock >= (int) $u->conversion_to_sub_unit && $subStock > 0,
        ])->values();
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
        <div x-data="{ unitId: @js($default?->id), qty: 1, units: @js($unitData) }" class="mt-6">
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

            <p class="mt-4 text-xl font-bold text-content" dir="ltr" x-text="units.find(u => u.id === unitId)?.price"></p>

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
