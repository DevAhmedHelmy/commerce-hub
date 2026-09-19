<x-layouts.app :title="__('orders.success_title')">
    <x-slot:header>
        <div class="px-4 py-3"><span class="text-lg font-bold text-content">{{ __('orders.success_title') }}</span></div>
    </x-slot:header>

    @php $fmt = fn ($m) => \App\Domain\Support\MoneyFormatter::format(\App\Domain\Support\Money::fromMinor((int) $m)); @endphp

    <div class="rounded-[--radius-md] border border-success/40 bg-success/10 p-6 text-center">
        <p class="text-4xl" aria-hidden="true">✓</p>
        <p class="mt-2 text-lg font-bold text-content">{{ $order->order_number }}</p>
        <p class="mt-1 text-sm text-muted">{{ __('orders.success_note') }}</p>
    </div>

    <div class="mt-4 space-y-1 rounded-[--radius-md] border border-border p-4 text-sm">
        <div class="flex justify-between"><span class="text-muted">{{ __('checkout.total') }}</span><span dir="ltr" class="font-bold text-content">{{ $fmt($order->final_total) }}</span></div>
        <div class="flex justify-between"><span class="text-muted">{{ __('orders.delivery_date') }}</span><span class="text-content">{{ optional($order->delivery_date)->format('Y-m-d') }} — {{ $order->delivery_slot_label }}</span></div>
    </div>

    <a href="{{ route('orders.show', $order) }}" class="mt-5 block w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-center text-base font-semibold text-inverse hover:bg-primary-strong">
        {{ __('orders.view') }}
    </a>

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
