@props([
    'title' => null,
    'message' => null,
    'icon' => true,
])

@php
    $title ??= __('messages.states.empty_title');
    $message ??= __('messages.states.empty_body');
@endphp

{{-- Calm empty state (design §5.24) — never styled as an error. Optional {{ $slot }}
     carries a primary action (e.g. "Browse products"). --}}
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 px-6 py-12 text-center']) }}>
    @if ($icon)
        <svg class="h-12 w-12 text-ink-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
            <path d="M3 9h18M8 14h8"></path>
        </svg>
    @endif
    <p class="text-lg font-semibold text-ink-900">{{ $title }}</p>
    <p class="max-w-sm text-sm text-ink-500">{{ $message }}</p>

    @if ($slot->isNotEmpty())
        <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
