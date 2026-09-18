@php
    // Direction derives from the active locale only — never from manually reversed
    // Arabic strings (design §8.1). Arabic (RTL) is the sole MVP locale; a future
    // LTR locale flips the whole shell with no redesign.
    $locale = app()->getLocale();
    $dir = in_array($locale, ['ar', 'he', 'fa', 'ur'], true) ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#137A46">
    <title>{{ $title ?? __('messages.app_name') }}</title>

    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-surface-100 font-sans text-ink-900 antialiased">
    {{-- Customer PWA shell: a mobile-first, centered single column. Logical spacing
         (px-*/text-start) mirrors automatically for a future LTR locale. --}}
    <div class="mx-auto flex min-h-screen w-full max-w-screen-sm flex-col bg-surface-0">
        @isset($header)
            <header class="sticky top-0 z-20 bg-surface-0/95 shadow-sm backdrop-blur">
                {{ $header }}
            </header>
        @endisset

        <main class="flex-1 px-4 py-4 text-start">
            {{ $slot }}
        </main>

        @isset($nav)
            <nav class="sticky bottom-0 z-20 bg-surface-0 shadow-[0_-2px_8px_rgba(15,23,42,0.08)]">
                {{ $nav }}
            </nav>
        @endisset
    </div>

    @stack('scripts')
</body>
</html>
