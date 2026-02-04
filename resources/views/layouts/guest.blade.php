<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Menut') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/menut-logo.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/menut-logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|dm-serif-display:400,400i&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-bark-800 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cream-50 leaf-pattern">
            <div>
                <a href="/" wire:navigate>
                    <x-application-logo class="w-40 h-40" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white border border-cream-200 shadow-soft overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
