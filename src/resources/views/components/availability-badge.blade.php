@props(['status'])

@php
    $value = $status instanceof \App\Domain\Support\Enums\AvailabilityStatus
        ? $status
        : \App\Domain\Support\Enums\AvailabilityStatus::from($status);
    $inactive = \App\Domain\Support\Enums\AvailabilityStatus::Inactive;
    $available = \App\Domain\Support\Enums\AvailabilityStatus::Available;
@endphp

{{-- Inactive products are never rendered to customers, so no badge for that state. --}}
@if ($value !== $inactive)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-[--radius-sm] px-2 py-0.5 text-xs font-semibold '
        . ($value === $available ? 'bg-success/10 text-success' : 'bg-surface-muted text-muted')]) }}>
        {{ __($value->labelKey()) }}
    </span>
@endif
