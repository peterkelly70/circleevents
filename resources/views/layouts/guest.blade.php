<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CircleEvents') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-[radial-gradient(circle_at_top_left,_rgba(251,146,60,0.2),_transparent_30%),linear-gradient(180deg,_#292524,_#0c0a09)] px-6 py-10 text-stone-100">
            <div>
                <a href="/">
                    <x-application-logo class="h-20 w-20 fill-current text-amber-300" />
                </a>
            </div>

            <div class="mt-6 w-full overflow-hidden rounded-[2rem] border border-white/10 bg-stone-900/85 px-6 py-5 text-stone-100 shadow-2xl shadow-black/30 backdrop-blur sm:max-w-md">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
