<x-layouts.app :title="__('checkout.title')">
    <x-slot:header>
        <div class="px-4 py-3"><span class="text-lg font-bold text-content">{{ __('checkout.delivery_step') }}</span></div>
    </x-slot:header>

    <form method="POST" action="{{ route('checkout.delivery.store') }}" class="space-y-5">
        @csrf

        <div>
            <p class="mb-2 text-sm font-semibold text-content">{{ __('checkout.address') }}</p>
            @forelse ($addresses as $address)
                <label class="mb-2 flex items-start gap-2 rounded-[--radius-sm] border border-border p-3">
                    <input type="radio" name="address_id" value="{{ $address->id }}" @checked($address->is_default) required class="mt-1">
                    <span class="text-sm text-content" dir="auto">{{ $address->address_line }}
                        @if ($address->building) — {{ $address->building }} @endif
                    </span>
                </label>
            @empty
                <a href="{{ route('onboarding.address') }}" class="text-sm text-primary">أضف عنوان توصيل</a>
            @endforelse
        </div>

        <div>
            <label for="delivery_date" class="mb-1 block text-sm font-semibold text-content">{{ __('checkout.delivery_date') }}</label>
            <input type="date" id="delivery_date" name="delivery_date" value="{{ $defaultDate }}" min="{{ $defaultDate }}" required
                class="w-full rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content">
        </div>

        <div>
            <label for="slot_id" class="mb-1 block text-sm font-semibold text-content">{{ __('checkout.slot') }}</label>
            <select id="slot_id" name="slot_id" required class="w-full rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content">
                @foreach ($slots as $slot)
                    <option value="{{ $slot->id }}">{{ $slot->localized('label') }} ({{ $slot->start_time }}–{{ $slot->end_time }})</option>
                @endforeach
            </select>
        </div>

        <p class="text-sm text-muted">{{ __('checkout.payment') }}: {{ __('checkout.cod') }}</p>

        <button type="submit" class="w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-base font-semibold text-inverse hover:bg-primary-strong">
            {{ __('checkout.continue') }}
        </button>
    </form>

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
