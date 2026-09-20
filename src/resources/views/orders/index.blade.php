<x-layouts.app :title="__('orders.title')">
    <x-slot:header>
        <div class="px-4 py-3"><span class="text-lg font-bold text-content">{{ __('orders.title') }}</span></div>
    </x-slot:header>

    @php $fmt = fn ($m) => \App\Domain\Support\MoneyFormatter::format($m); @endphp

    @forelse ($orders as $order)
        <a href="{{ route('orders.show', $order) }}" class="mb-3 block rounded-[--radius-md] border border-border p-4">
            <div class="flex items-center justify-between">
                <span class="font-bold text-content">{{ $order->order_number }}</span>
                <span class="rounded-[--radius-sm] bg-surface-muted px-2 py-0.5 text-xs text-content">{{ __($order->status->labelKey()) }}</span>
            </div>
            <div class="mt-1 flex items-center justify-between text-sm text-muted">
                <span>{{ $order->created_at?->format('Y-m-d') }}</span>
                <span dir="ltr" class="font-semibold text-content">{{ $fmt($order->finalTotalMoney()) }}</span>
            </div>
        </a>
    @empty
        <div class="py-16 text-center text-muted">{{ __('orders.empty') }}</div>
    @endforelse

    @if ($orders->hasPages())
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
