<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Menut - Organitzador de Menús Setmanals</title>

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#536b3c">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Menut">
        <meta name="application-name" content="Menut">

        <!-- Web App Manifest -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">

        <!-- Favicon & Icons -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/icons/icon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/icons/icon-192x192.png') }}">

        <!-- iOS Splash Screens (apple-touch-startup-image) -->
        <link rel="apple-touch-startup-image" media="(device-width: 390px) and (device-height: 844px) and (-webkit-device-pixel-ratio: 3)" href="{{ asset('images/splash/splash-1170x2532.png') }}">
        <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)" href="{{ asset('images/splash/splash-1125x2436.png') }}">
        <link rel="apple-touch-startup-image" media="(device-width: 428px) and (device-height: 926px) and (-webkit-device-pixel-ratio: 3)" href="{{ asset('images/splash/splash-1284x2778.png') }}">
        <link rel="apple-touch-startup-image" media="(device-width: 393px) and (device-height: 852px) and (-webkit-device-pixel-ratio: 3)" href="{{ asset('images/splash/splash-1179x2556.png') }}">
        <link rel="apple-touch-startup-image" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)" href="{{ asset('images/splash/splash-750x1334.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|dm-serif-display:400,400i&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans bg-cream-50 leaf-pattern">
        <div class="min-h-screen">
            <div class="relative flex flex-col items-center justify-center min-h-screen selection:bg-forest-500 selection:text-white">
                <div class="relative w-full max-w-2xl px-6 lg:max-w-4xl">
                    <header class="flex items-center justify-between py-10">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/menut-favicon.png') }}" alt="Menut" class="w-20 h-20 object-contain">
                            <h1 class="text-2xl font-display text-bark-800">Menut</h1>
                        </div>
                        @if (Route::has('login'))
                            <nav class="flex items-center gap-4">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="btn-primary">
                                        Anar al tauler
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-bark-600 hover:text-bark-800 transition-colors">
                                        Iniciar sessió
                                    </a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="btn-primary">
                                            Registrar-se
                                        </a>
                                    @endif
                                @endauth
                            </nav>
                        @endif
                    </header>

                    <main class="mt-10 text-center">
                        <h2 class="text-4xl font-display text-bark-800 sm:text-5xl">
                            Organitza els teus menús setmanals
                        </h2>
                        <p class="mt-6 text-lg text-bark-600 max-w-xl mx-auto">
                            Planifica els àpats de la setmana, gestiona les teves receptes i digue adéu a la pregunta diària: "Què mengem avui?"
                        </p>

                        <div class="mt-10 flex justify-center gap-4">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="btn-primary px-6 py-3">
                                    Anar al tauler
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="btn-primary px-6 py-3">
                                    Comença ara
                                </a>
                                <a href="{{ route('login') }}" class="btn-secondary px-6 py-3">
                                    Ja tinc compte
                                </a>
                            @endauth
                        </div>

                        <div class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div class="p-6 bg-white rounded-2xl border border-cream-200 shadow-soft">
                                <div class="w-12 h-12 mx-auto flex items-center justify-center bg-forest-100 rounded-xl">
                                    <svg class="w-6 h-6 text-forest-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-bark-800">Vista setmanal</h3>
                                <p class="mt-2 text-sm text-bark-600">Visualitza el menú de tota la setmana d'un cop d'ull.</p>
                            </div>

                            <div class="p-6 bg-white rounded-2xl border border-cream-200 shadow-soft">
                                <div class="w-12 h-12 mx-auto flex items-center justify-center bg-forest-100 rounded-xl">
                                    <svg class="w-6 h-6 text-forest-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-bark-800">Gestió de receptes</h3>
                                <p class="mt-2 text-sm text-bark-600">Guarda les teves receptes preferides amb ingredients i instruccions.</p>
                            </div>

                            <div class="p-6 bg-white rounded-2xl border border-cream-200 shadow-soft">
                                <div class="w-12 h-12 mx-auto flex items-center justify-center bg-forest-100 rounded-xl">
                                    <svg class="w-6 h-6 text-forest-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-lg font-semibold text-bark-800">Fàcil assignació</h3>
                                <p class="mt-2 text-sm text-bark-600">Assigna receptes als àpats amb un sol clic.</p>
                            </div>
                        </div>
                    </main>

                    <footer class="py-16 text-center text-sm text-bark-400">
                        Menut - Fet amb Laravel
                    </footer>
                </div>
            </div>
        </div>
    </body>
</html>
