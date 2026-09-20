@props(['class' => 'h-8'])
@php
    $landing = app(\App\Domain\Landing\LandingPageService::class);
    $name = $landing->companyName();
    $logo = $landing->logoUrl('light');
@endphp

@if ($logo)
    <img src="{{ $logo }}" alt="{{ $name }}" {{ $attributes->merge(['class' => $class.' w-auto object-contain']) }}>
@else
    <span {{ $attributes->merge(['class' => 'text-lg font-extrabold text-content']) }} dir="auto">{{ $name }}</span>
@endif
