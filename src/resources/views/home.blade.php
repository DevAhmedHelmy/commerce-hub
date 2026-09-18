<x-layouts.app :title="__('messages.app_name')">
    {{-- Onboarding-gated entry placeholder. Phase D (T045) replaces this with the
         real home hub (C06): categories, search, offers. --}}
    <div class="flex flex-col items-center justify-center gap-3 py-16 text-center">
        <h1 class="text-2xl font-bold text-ink-900">{{ __('messages.app_name') }}</h1>
        <p class="text-sm text-ink-500">{{ __('auth.onboarding.complete') }}</p>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="text-sm font-semibold text-brand-600">{{ __('messages.actions.back') }}</button>
        </form>
    </div>
</x-layouts.app>
