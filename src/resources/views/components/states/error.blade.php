@props([
    'title' => null,
    'message' => null,
    'retryUrl' => null,
])

@php
    $title ??= __('messages.states.error_title');
    $message ??= __('messages.states.error_body');
@endphp

{{-- Recoverable error state (design §5.25) — plain message + Retry. The {{ $slot }}
     may override the retry affordance (e.g. an Alpine reload handler). --}}
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 px-6 py-12 text-center']) }} role="alert">
    <svg class="h-12 w-12 text-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <circle cx="12" cy="12" r="9"></circle>
        <path d="M12 8v4m0 4h.01"></path>
    </svg>
    <p class="text-lg font-semibold text-content">{{ $title }}</p>
    <p class="max-w-sm text-sm text-muted">{{ $message }}</p>

    @if ($slot->isNotEmpty())
        <div class="mt-2">{{ $slot }}</div>
    @elseif ($retryUrl)
        <a href="{{ $retryUrl }}" class="mt-2 inline-flex items-center rounded-[--radius-sm] bg-primary px-4 py-2 text-sm font-semibold text-inverse">
            {{ __('messages.actions.retry') }}
        </a>
    @endif
</div>
