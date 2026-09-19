<x-layouts.app :title="__('account.edit_profile')">
    <x-slot:header>
        <div class="px-4 py-3"><span class="text-lg font-bold text-content">{{ __('account.edit_profile') }}</span></div>
    </x-slot:header>

    <form method="POST" action="{{ route('account.profile.update') }}" class="space-y-4">
        @csrf
        <div>
            <label for="business_name" class="mb-1 block text-sm font-semibold text-content">{{ __('account.business_name') }}</label>
            <input type="text" id="business_name" name="business_name" value="{{ old('business_name', $customer->business_name) }}" required
                class="w-full rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content" dir="auto">
            @error('business_name') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="contact_person_name" class="mb-1 block text-sm font-semibold text-content">{{ __('account.contact_person') }}</label>
            <input type="text" id="contact_person_name" name="contact_person_name" value="{{ old('contact_person_name', $customer->contact_person_name) }}" required
                class="w-full rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content" dir="auto">
            @error('contact_person_name') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="whatsapp_phone" class="mb-1 block text-sm font-semibold text-content">{{ __('account.whatsapp') }}</label>
            <input type="tel" id="whatsapp_phone" name="whatsapp_phone" value="{{ old('whatsapp_phone', $customer->whatsapp_phone) }}"
                class="w-full rounded-[--radius-sm] border border-border bg-surface px-3 py-2 text-content" dir="ltr">
        </div>
        <button type="submit" class="w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-base font-semibold text-inverse hover:bg-primary-strong">{{ __('account.save') }}</button>
    </form>

    <x-slot:nav><x-catalog-nav /></x-slot:nav>
</x-layouts.app>
