<x-layouts.public :title="__('auth.screens.login_title')">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <h1 class="text-2xl font-bold text-content">{{ __('auth.screens.login_title') }}</h1>
        <p class="mt-2 text-sm text-muted">{{ __('auth.screens.login_subtitle') }}</p>

        @if (session('status'))
            <div class="mt-4 rounded-[--radius-sm] bg-success/10 px-4 py-3 text-sm text-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('otp.request') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="phone" class="mb-1 block text-sm font-semibold text-content">{{ __('auth.fields.phone') }}</label>
                <input id="phone" name="phone" type="tel" inputmode="tel" dir="ltr" value="{{ old('phone') }}"
                    autofocus required
                    class="w-full rounded-[--radius-sm] border border-border-strong bg-surface px-4 py-3 text-base text-content focus:border-focus focus:ring-2 focus:ring-focus @error('phone') border-danger @enderror">
                @error('phone')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-base font-semibold text-inverse hover:bg-primary-strong">
                {{ __('auth.actions.send_code') }}
            </button>
        </form>
    </div>
</x-layouts.public>
