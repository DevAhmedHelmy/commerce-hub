<x-layouts.app :title="__('account.title')">
    <x-slot:header>
        <div class="px-4 py-3"><span class="text-lg font-bold text-content">{{ __('account.title') }}</span></div>
    </x-slot:header>

    @if (session('status'))
        <div class="mb-3 rounded-[--radius-sm] bg-success/10 px-3 py-2 text-sm text-success">{{ session('status') }}</div>
    @endif

    <section class="rounded-[--radius-md] border border-border p-4">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="font-bold text-content">{{ __('account.business_info') }}</h2>
            <a href="{{ route('account.profile') }}" class="text-sm text-primary">{{ __('account.edit_profile') }}</a>
        </div>
        <dl class="space-y-1 text-sm">
            <div class="flex justify-between"><dt class="text-muted">{{ __('account.business_name') }}</dt><dd class="text-content" dir="auto">{{ $customer->business_name }}</dd></div>
            <div class="flex justify-between"><dt class="text-muted">{{ __('account.contact_person') }}</dt><dd class="text-content" dir="auto">{{ $customer->contact_person_name }}</dd></div>
            <div class="flex justify-between"><dt class="text-muted">{{ __('account.phone') }}</dt><dd class="text-content" dir="ltr">{{ $customer->phone }}</dd></div>
            <div class="flex justify-between"><dt class="text-muted">{{ __('account.whatsapp') }}</dt><dd class="text-content" dir="ltr">{{ $customer->whatsapp_phone ?: '—' }}</dd></div>
        </dl>
    </section>

    <section class="mt-4 rounded-[--radius-md] border border-border p-4">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="font-bold text-content">{{ __('account.delivery_address') }}</h2>
            <a href="{{ route('account.address') }}" class="text-sm text-primary">{{ __('account.edit_address') }}</a>
        </div>
        @if ($address)
            <dl class="space-y-1 text-sm">
                <div class="flex justify-between"><dt class="text-muted">{{ __('account.area') }}</dt><dd class="text-content" dir="auto">{{ $address->deliveryArea?->localized('name') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-muted">{{ __('account.address') }}</dt><dd class="text-content" dir="auto">{{ $address->address_line }}</dd></div>
            </dl>
        @else
            <p class="text-sm text-muted">—</p>
        @endif
    </section>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="w-full rounded-[--radius-sm] border border-border px-4 py-3 text-sm font-semibold text-content">{{ __('account.logout') }}</button>
    </form>

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
