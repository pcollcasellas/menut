<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm border-b border-cream-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2">
                        <img src="{{ asset('images/menut-logo.png') }}" alt="Menut" class="w-14 h-14 object-contain">
                        <span class="font-display text-lg text-bark-800">Menut</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-1 sm:-my-px sm:ms-8 sm:flex">
                    <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'text-forest-700 bg-forest-50' : 'text-bark-600 hover:text-bark-800 hover:bg-cream-100' }}">
                        Menú Setmanal
                    </a>
                    <a href="{{ route('recipes') }}" wire:navigate class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-xl transition-colors {{ request()->routeIs('recipes') ? 'text-forest-700 bg-forest-50' : 'text-bark-600 hover:text-bark-800 hover:bg-cream-100' }}">
                        Receptes
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium text-bark-600 hover:text-bark-800 rounded-xl hover:bg-cream-100 focus:outline-none transition-colors">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            Perfil
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                Tancar sessió
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-bark-500 hover:text-bark-700 hover:bg-cream-100 focus:outline-none focus:bg-cream-100 focus:text-bark-700 transition-colors">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-cream-200">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}" wire:navigate class="block px-3 py-2 text-base font-medium rounded-xl {{ request()->routeIs('dashboard') ? 'text-forest-700 bg-forest-50' : 'text-bark-600 hover:text-bark-800 hover:bg-cream-100' }}">
                Menú Setmanal
            </a>
            <a href="{{ route('recipes') }}" wire:navigate class="block px-3 py-2 text-base font-medium rounded-xl {{ request()->routeIs('recipes') ? 'text-forest-700 bg-forest-50' : 'text-bark-600 hover:text-bark-800 hover:bg-cream-100' }}">
                Receptes
            </a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-cream-200">
            <div class="px-4">
                <div class="font-medium text-base text-bark-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-bark-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1 px-4">
                <a href="{{ route('profile') }}" wire:navigate class="block px-3 py-2 text-base font-medium text-bark-600 hover:text-bark-800 hover:bg-cream-100 rounded-xl">
                    Perfil
                </a>
                <button wire:click="logout" class="w-full text-start block px-3 py-2 text-base font-medium text-bark-600 hover:text-bark-800 hover:bg-cream-100 rounded-xl">
                    Tancar sessió
                </button>
            </div>
        </div>
    </div>
</nav>
