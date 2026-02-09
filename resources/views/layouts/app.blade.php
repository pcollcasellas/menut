<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Menut') }}</title>

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#536b3c">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Menut">
        <meta name="application-name" content="Menut">
        <meta name="msapplication-TileColor" content="#536b3c">
        <meta name="msapplication-TileImage" content="{{ asset('images/icons/icon-144x144.png') }}">

        <!-- Web App Manifest -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">

        <!-- Favicon & Icons -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icons/icon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icons/icon-192x192.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|dm-serif-display:400,400i&display=swap" rel="stylesheet" />

        <!-- Flux Appearance - Force light mode for this app -->
        @fluxAppearance
        <script>
            window.Flux && window.Flux.applyAppearance('light');
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-cream-50 leaf-pattern">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white/80 backdrop-blur-sm border-b border-cream-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="animate-fade-in pb-16 sm:pb-0">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="fixed inset-x-0 bottom-0 z-20 border-t border-cream-200 bg-cream-50/90 py-3 text-center backdrop-blur-sm sm:static sm:z-auto sm:mt-auto sm:border-t-0 sm:bg-transparent sm:py-6 sm:backdrop-blur-0">
                <p class="text-xs text-bark-500 sm:text-sm">
                    Menut &middot; Organitzador de menús setmanals
                </p>
            </footer>
        </div>

        <!-- Flux Scripts -->
        @fluxScripts
    </body>
</html>
