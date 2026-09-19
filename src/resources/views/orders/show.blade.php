<x-layouts.app :title="$order->order_number">
    <x-slot:header>
        <div class="px-4 py-3"><span class="text-lg font-bold text-content">{{ $order->order_number }}</span></div>
    </x-slot:header>

    @php $fmt = fn ($m) => \App\Domain\Support\MoneyFormatter::format(\App\Domain\Support\Money::fromMinor((int) $m)); @endphp

    @if (session('status'))
        <div class="mb-3 rounded-[--radius-sm] bg-success/10 px-3 py-2 text-sm text-success">{{ session('status') }}</div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <span class="rounded-[--radius-sm] bg-surface-muted px-3 py-1 text-sm text-content">{{ __($order->status->labelKey()) }}</span>
        <span class="text-sm text-muted">{{ $order->created_at?->format('Y-m-d H:i') }}</span>
    </div>

    <div class="space-y-2">
        @foreach ($order->items as $item)
            <div class="flex items-center justify-between rounded-[--radius-sm] border border-border p-3 text-sm">
                <span class="text-content" dir="auto">{{ $item->product_name }} — {{ $item->unit_name }} × {{ $item->quantity }}</span>
                <span class="font-semibold text-content" dir="ltr">{{ $fmt($item->line_total) }}</span>
            </div>
        @endforeach
    </div>

    <div class="mt-4 space-y-1 rounded-[--radius-md] border border-border p-4 text-sm">
        <div class="flex justify-between"><span class="text-muted">{{ __('checkout.product_subtotal') }}</span><span dir="ltr" class="text-content">{{ $fmt($order->product_subtotal) }}</span></div>
        <div class="flex justify-between"><span class="text-muted">{{ __('checkout.delivery_fee') }}</span><span dir="ltr" class="text-content">{{ $fmt($order->final_delivery_fee) }}</span></div>
        <div class="flex justify-between border-t border-border pt-2 text-base font-bold"><span class="text-content">{{ __('checkout.total') }}</span><span dir="ltr" class="text-content">{{ $fmt($order->final_total) }}</span></div>
    </div>

    <div class="mt-4 rounded-[--radius-md] border border-border p-4 text-sm text-muted">
        <p>{{ __('orders.delivery_date') }}: {{ optional($order->delivery_date)->format('Y-m-d') }} — {{ $order->delivery_slot_label }}</p>
        <p class="mt-1" dir="auto">{{ __('orders.address') }}: {{ $order->delivery_area_name }} — {{ $order->address_line }}</p>
        <p class="mt-1">{{ __('orders.cod') }}</p>
    </div>

    @if ($canCancel)
        <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full rounded-[--radius-sm] border border-danger px-4 py-3 text-base font-semibold text-danger">
                {{ __('orders.cancel') }}
            </button>
        </form>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
