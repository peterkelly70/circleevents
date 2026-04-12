<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="google-maps-api-key" content="{{ config('services.google_maps.key') }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Cormorant+Garamond:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Orbitron:wght@500;700;800&family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">

        <title>{{ config('app.name', 'CircleEvents') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @if (config('services.google_maps.key'))
            <script async src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google_maps.key')) }}&libraries=places&v=beta"></script>
        @endif
    </head>
    <body class="antialiased">
        @php
            $personalThemeKey = auth()->user()?->personal_theme ?? 'embers';
            $personalTheme = \App\Support\OrganizationThemes::get($personalThemeKey);
        @endphp
        <div class="min-h-screen {{ $personalTheme['page_backdrop'] }} text-stone-100 {{ auth()->user()?->fontSizeClass() ?? 'text-base leading-7' }}">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b {{ $personalTheme['panel'] }} {{ $personalTheme['page_backdrop'] }}">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
