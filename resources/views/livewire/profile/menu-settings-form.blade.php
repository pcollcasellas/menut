<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $showBreakfast = false;

    public function mount(): void
    {
        $this->showBreakfast = Auth::user()->show_breakfast ?? false;
    }

    public function updateMenuSettings(): void
    {
        $user = Auth::user();
        $user->show_breakfast = $this->showBreakfast;
        $user->save();

        $this->dispatch('menu-settings-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-bark-800">
            {{ __('Configuració del menú') }}
        </h2>

        <p class="mt-1 text-sm text-bark-600">
            {{ __('Personalitza les opcions del teu menú setmanal.') }}
        </p>
    </header>

    <form wire:submit="updateMenuSettings" class="mt-6 space-y-6">
        <flux:field>
            <flux:switch
                wire:model="showBreakfast"
                label="Mostrar esmorzar"
                description="Afegeix una fila d'esmorzar al menú setmanal" />
        </flux:field>

        <div class="flex items-center gap-4">
            <flux:button type="submit" variant="primary">{{ __('Desar') }}</flux:button>

            <x-action-message class="me-3" on="menu-settings-updated">
                {{ __('Desat.') }}
            </x-action-message>
        </div>
    </form>
</section>
