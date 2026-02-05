<x-app-layout>
    <x-slot name="header">
        <h2 class="font-display text-2xl text-bark-800 leading-tight">
            Llar
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white border border-cream-200 shadow-soft rounded-2xl">
                <div class="max-w-xl">
                    <livewire:household-manager />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
