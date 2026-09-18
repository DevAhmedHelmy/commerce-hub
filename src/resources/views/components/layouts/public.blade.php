@php
    // Direction derives from the active locale only (design §8.1). Public shell for
    // the landing page and auth screens — full-width, no app navigation.
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
<body class="min-h-screen bg-surface-0 font-sans text-ink-900 antialiased">
    <div class="mx-auto flex min-h-screen w-full max-w-screen-xl flex-col text-start">
        {{ $slot }}
    </div>

    @stack('scripts')
</body>
</html>
