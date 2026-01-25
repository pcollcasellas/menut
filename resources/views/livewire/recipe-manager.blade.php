<div>
    <!-- Capçalera -->
    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl">Receptes</flux:heading>
        @if(!$showForm)
            <flux:button wire:click="create" variant="primary" icon="plus">
                Nova recepta
            </flux:button>
        @endif
    </div>

    <!-- Formulari -->
    @if($showForm)
        <div class="bg-white border border-stone-200 rounded-lg p-6 mb-6">
            <flux:heading size="lg" class="mb-4">
                {{ $editingId ? 'Editar recepta' : 'Nova recepta' }}
            </flux:heading>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <flux:input
                        wire:model="name"
                        label="Nom *"
                        name="name"
                    />
                    <flux:error name="name" />
                </div>

                <div>
                    <flux:textarea
                        wire:model="description"
                        label="Descripció"
                        name="description"
                        rows="2"
                    />
                </div>

                <div>
                    <flux:textarea
                        wire:model="ingredients"
                        label="Ingredients"
                        name="ingredients"
                        placeholder="Un ingredient per línia"
                        rows="4"
                    />
                </div>

                <div>
                    <flux:textarea
                        wire:model="instructions"
                        label="Instruccions"
                        name="instructions"
                        rows="4"
                    />
                </div>

                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
                    <flux:button type="button" wire:click="cancel" variant="ghost">
                        Cancel·lar
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? 'Actualitzar' : 'Crear' }}
                    </flux:button>
                </div>
            </form>
        </div>
    @endif

    <!-- Llista de receptes -->
    <div class="bg-white border border-stone-200 rounded-lg overflow-hidden">
        @if($recipes->isEmpty())
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <flux:text class="text-stone-500">
                    No hi ha receptes. Crea la primera!
                </flux:text>
            </div>
        @else
            <ul class="divide-y divide-stone-200">
                @foreach($recipes as $recipe)
                    <li class="p-4 hover:bg-stone-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <flux:text class="font-medium text-stone-800">{{ $recipe->name }}</flux:text>
                                @if($recipe->description)
                                    <flux:text size="sm" class="text-stone-500 truncate mt-0.5">{{ $recipe->description }}</flux:text>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 ml-4">
                                <flux:button wire:click="edit({{ $recipe->id }})" variant="ghost" icon="pencil" square size="sm" />
                                <flux:button
                                    wire:click="delete({{ $recipe->id }})"
                                    wire:confirm="Segur que vols eliminar aquesta recepta?"
                                    variant="ghost"
                                    icon="trash"
                                    square
                                    size="sm"
                                    class="text-stone-500 hover:text-red-600 hover:bg-red-50"
                                />
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
