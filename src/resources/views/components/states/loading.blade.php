@props([
    'variant' => 'spinner', {{-- spinner | skeleton --}}
    'rows' => 3,
    'label' => null,
])

@php $label ??= __('messages.states.loading'); @endphp

@if ($variant === 'skeleton')
    {{-- Shape-matching skeleton (design §5.23): calm surface blocks, subtle pulse. --}}
    <div {{ $attributes->merge(['class' => 'space-y-3']) }} role="status" aria-label="{{ $label }}">
        @for ($i = 0; $i < (int) $rows; $i++)
            <div class="animate-pulse rounded-[--radius-md] bg-surface-muted p-4">
                <div class="h-3 w-1/3 rounded bg-border"></div>
                <div class="mt-3 h-3 w-2/3 rounded bg-border"></div>
            </div>
        @endfor
        <span class="sr-only">{{ $label }}</span>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center gap-3 py-8 text-muted']) }} role="status" aria-live="polite">
        <svg class="h-5 w-5 animate-spin text-primary" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
        </svg>
        <span>{{ $label }}</span>
    </div>
@endif
