@props([
    'money' => null,
    'minor' => null,
    'currency' => \App\Domain\Support\Money::CURRENCY,
])

@php
    $value = $money instanceof \App\Domain\Support\Money
        ? $money
        : \App\Domain\Support\Money::fromMinor((int) ($minor ?? 0), $currency);
@endphp

{{-- Centralized currency rendering. dir="ltr" keeps the grouped Latin number
     followed by the symbol (444 ج) rendering correctly inside an RTL layout;
     tabular figures keep price columns aligned (design §2/§8.4). --}}
<span {{ $attributes->merge(['class' => 'tabular-nums whitespace-nowrap']) }} dir="ltr">{{ \App\Domain\Support\MoneyFormatter::format($value) }}</span>
