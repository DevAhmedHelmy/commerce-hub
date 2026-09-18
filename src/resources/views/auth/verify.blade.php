<x-layouts.public :title="__('auth.screens.verify_title')">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('auth.screens.verify_title') }}</h1>
        <p class="mt-2 text-sm text-ink-500">{{ __('auth.screens.verify_subtitle', ['phone' => $phone]) }}</p>

        @if (session('status'))
            <div class="mt-4 rounded-[--radius-sm] bg-success-100 px-4 py-3 text-sm text-success-600">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="code" class="mb-1 block text-sm font-semibold text-ink-700">{{ __('auth.fields.code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                    dir="ltr" autofocus required
                    class="w-full rounded-[--radius-sm] border border-line-300 px-4 py-3 text-center text-lg tracking-[0.4em] tabular-nums focus:border-brand-600 focus:ring-2 focus:ring-brand-600 @error('code') border-danger-600 @enderror">
                @error('code')
                    <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full rounded-[--radius-sm] bg-brand-600 px-4 py-3 text-base font-semibold text-white hover:bg-brand-500">
                {{ __('auth.actions.verify') }}
            </button>
        </form>

        <form method="POST" action="{{ route('otp.resend') }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full text-sm font-semibold text-brand-600 hover:text-brand-500">
                {{ __('auth.actions.resend') }}
            </button>
        </form>
    </div>
</x-layouts.public>
