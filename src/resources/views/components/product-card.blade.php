@props(['product'])

@php
    $unit = $product->baselineUnit();
    $image = $product->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($product->image_path) : null;
@endphp

{{-- Product card (design §5.6): opens Product Details. It MUST NOT add to cart or pick a
     unit — unit/quantity/price are chosen on the detail screen (prompt Phase D). --}}
<a href="{{ route('products.show', $product) }}"
    class="flex flex-col rounded-[--radius-md] border border-border bg-surface p-3 shadow-[var(--shadow-card)] transition hover:border-border-strong">
    <div class="flex aspect-square items-center justify-center overflow-hidden rounded-[--radius-sm] bg-surface-muted">
        @if ($image)
            <img src="{{ $image }}" alt="{{ $product->localized('name') }}" loading="lazy" class="h-full w-full object-contain">
        @else
            <span class="text-3xl font-bold text-muted" aria-hidden="true">{{ mb_substr($product->localized('name'), 0, 1) }}</span>
        @endif
    </div>

    @if ($product->brand)
        <p class="mt-2 text-xs text-muted" dir="auto">{{ $product->brand }}</p>
    @endif

    <p class="text-sm font-semibold text-content" dir="auto">{{ $product->localized('name') }}</p>

    <div class="mt-1">
        <x-availability-badge :status="$product->availability" />
    </div>

    @if ($unit)
        <p class="mt-2 text-xs text-muted">
            {{ __('catalog.from') }}
            <x-price :money="$unit->basePriceMoney()" class="text-sm font-bold text-content" />
        </p>
    @endif
</a>
