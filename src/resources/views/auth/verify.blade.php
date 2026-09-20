<x-layouts.public :title="__('auth.screens.verify_title')">
    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col justify-center px-4 py-10">
        <h1 class="text-2xl font-bold text-content">{{ __('auth.screens.verify_title') }}</h1>
        <p class="mt-2 text-sm text-muted">{{ __('auth.screens.verify_subtitle', ['phone' => $phone]) }}</p>

        @if (session('status'))
            <div class="mt-4 rounded-[--radius-sm] bg-success/10 px-4 py-3 text-sm text-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('otp.verify') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="code" class="mb-1 block text-sm font-semibold text-content">{{ __('auth.fields.code') }}</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                    dir="ltr" autofocus required
                    class="w-full rounded-[--radius-sm] border border-border-strong bg-surface px-4 py-3 text-center text-lg tracking-[0.4em] tabular-nums text-content focus:border-focus focus:ring-2 focus:ring-focus @error('code') border-danger @enderror">
                @error('code')
                    <p class="mt-1 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full rounded-[--radius-sm] bg-primary px-4 py-3 text-base font-semibold text-inverse hover:bg-primary-strong">
                {{ __('auth.actions.verify') }}
            </button>
        </form>

        <form method="POST" action="{{ route('otp.resend') }}" class="mt-4">
            @csrf
            <button type="submit" class="w-full text-sm font-semibold text-primary hover:text-primary-strong">
                {{ __('auth.actions.resend') }}
            </button>
        </form>
    </div>
</x-layouts.public>
