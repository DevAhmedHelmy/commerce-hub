<x-layouts.public :title="__('auth.screens.profile_title')">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <h1 class="text-2xl font-bold text-content">{{ __('auth.screens.profile_title') }}</h1>

        <form method="POST" action="{{ route('onboarding.profile.save') }}" class="mt-6 space-y-4">
            @csrf
            @php
                $fields = [
                    ['business_name', 'text', true],
                    ['contact_person_name', 'text', true],
                    ['whatsapp_phone', 'tel', false],
                ];
            @endphp
            @foreach ($fields as [$name, $type, $required])
                <div>
                    <label for="{{ $name }}" class="mb-1 block text-sm font-semibold text-content">{{ __('auth.fields.'.$name) }}</label>
                    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $customer->{$name}) }}"
                        @if ($type === 'tel') dir="ltr" @endif @if ($required) required @endif
                        class="w-full rounded-[--radius-sm] border border-border-strong bg-surface px-4 py-3 text-base text-content focus:border-focus focus:ring-2 focus:ring-focus @error($name) border-danger @enderror">
                    @error($name)
                        <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <button type="submit"
                class="w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-base font-semibold text-inverse hover:bg-primary-strong">
                {{ __('auth.actions.continue') }}
            </button>
        </form>
    </div>
</x-layouts.public>
