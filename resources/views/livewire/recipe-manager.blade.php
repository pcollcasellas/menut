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
                    <flux:label>Ingredients</flux:label>

                    {{-- Selected ingredients as tags --}}
                    @if(count($selectedIngredients) > 0)
                        <div class="flex flex-wrap gap-2 mb-2 mt-1">
                            @foreach($selectedIngredients as $index => $ingredient)
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-forest-100 text-forest-800 rounded-full text-sm">
                                    {{ $ingredient }}
                                    <button
                                        type="button"
                                        wire:click="removeIngredient({{ $index }})"
                                        class="ml-1 text-forest-600 hover:text-forest-800 focus:outline-none"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Input with autocomplete --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <flux:input
                            wire:model.live.debounce.300ms="ingredientSearch"
                            wire:keydown.enter.prevent="addIngredient"
                            placeholder="Escriu un ingredient i prem Enter..."
                            @focus="open = true"
                            @input="open = true"
                            autocomplete="off"
                        />

                        {{-- Autocomplete suggestions --}}
                        @if(count($this->ingredientSuggestions) > 0)
                            <div
                                x-show="open"
                                x-cloak
                                class="absolute z-10 w-full mt-1 bg-white border border-stone-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"
                            >
                                @foreach($this->ingredientSuggestions as $suggestion)
                                    <button
                                        type="button"
                                        wire:click="selectSuggestion('{{ addslashes($suggestion) }}')"
                                        class="w-full px-4 py-2 text-left text-sm hover:bg-cream-100 focus:bg-cream-100 focus:outline-none transition-colors"
                                    >
                                        {{ $suggestion }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <flux:text size="xs" class="text-stone-500 mt-1">
                        Escriu i prem Enter per afegir. Les suggerències apareixeran dels ingredients existents.
                    </flux:text>
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
                                @if($recipe->ingredientItems->count() > 0)
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        @foreach($recipe->ingredientItems->take(5) as $ingredient)
                                            <span class="inline-flex px-2 py-0.5 bg-cream-100 text-bark-600 rounded-full text-xs">
                                                {{ $ingredient->name }}
                                            </span>
                                        @endforeach
                                        @if($recipe->ingredientItems->count() > 5)
                                            <span class="inline-flex px-2 py-0.5 bg-stone-100 text-stone-500 rounded-full text-xs">
                                                +{{ $recipe->ingredientItems->count() - 5 }}
                                            </span>
                                        @endif
                                    </div>
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
