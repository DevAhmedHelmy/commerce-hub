<x-layouts.app :title="__('checkout.review_step')">
    <x-slot:header>
        <div class="px-4 py-3"><span class="text-lg font-bold text-content">{{ __('checkout.review_step') }}</span></div>
    </x-slot:header>

    @php $fmt = fn ($m) => \App\Domain\Support\MoneyFormatter::format($m); @endphp

    @if (session('status'))
        <div class="mb-3 rounded-[--radius-sm] bg-warning/10 px-3 py-2 text-sm text-warning">{{ session('status') }}</div>
    @endif

    @foreach ($review->changes as $key)
        <div class="mb-2 rounded-[--radius-sm] bg-warning/10 px-3 py-2 text-sm text-warning">{{ __($key) }}</div>
    @endforeach
    @foreach ($review->blockers as $key)
        <div class="mb-2 rounded-[--radius-sm] bg-danger/10 px-3 py-2 text-sm text-danger">{{ __($key) }}</div>
    @endforeach

    <div class="space-y-2">
        @foreach ($review->lines as $line)
            <div class="flex items-center justify-between rounded-[--radius-sm] border border-border p-3 text-sm">
                <span class="text-content" dir="auto">{{ $line->unit->product?->localized('name') }} — {{ $line->unit->label() }} × {{ $line->item->quantity }}</span>
                <span class="font-semibold text-content" dir="ltr">{{ $fmt($line->price->lineTotal) }}</span>
            </div>
        @endforeach
    </div>

    <div class="mt-4 space-y-1 rounded-[--radius-md] border border-border p-4 text-sm">
        <div class="flex justify-between"><span class="text-muted">{{ __('checkout.product_subtotal') }}</span><span dir="ltr" class="text-content">{{ $fmt($review->productSubtotal) }}</span></div>
        @if ($review->deliveryQuote)
            <div class="flex justify-between"><span class="text-muted">{{ __('checkout.delivery_fee') }}</span><span dir="ltr" class="text-content">{{ $fmt($review->deliveryQuote->baseFee) }}</span></div>
            @if (! $review->deliveryQuote->discountAmount->isZero())
                <div class="flex justify-between"><span class="text-muted">{{ __('checkout.delivery_discount') }}</span><span dir="ltr" class="text-success">− {{ $fmt($review->deliveryQuote->discountAmount) }}</span></div>
            @endif
        @endif
        <div class="flex justify-between border-t border-border pt-2 text-base font-bold"><span class="text-content">{{ __('checkout.total') }}</span><span dir="ltr" class="text-content">{{ $fmt($review->finalTotal) }}</span></div>
    </div>

    <form method="POST" action="{{ route('checkout.confirm') }}" class="mt-5">
        @csrf
        <button type="submit" @disabled(! $review->canPlace())
            @class(['w-full rounded-[--radius-sm] px-4 py-3 text-base font-semibold', 'bg-primary text-inverse hover:bg-primary-strong' => $review->canPlace(), 'bg-surface-muted text-muted' => ! $review->canPlace()])>
            {{ __('checkout.confirm') }}
        </button>
    </form>

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
