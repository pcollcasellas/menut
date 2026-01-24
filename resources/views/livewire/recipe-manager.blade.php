<div>
    <!-- Capçalera -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-stone-800">Receptes</h2>
        @if(!$showForm)
            <button wire:click="create" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                Nova recepta
            </button>
        @endif
    </div>

    <!-- Formulari -->
    @if($showForm)
        <div class="bg-white border border-stone-200 rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-stone-800 mb-4">
                {{ $editingId ? 'Editar recepta' : 'Nova recepta' }}
            </h3>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-stone-700">Nom *</label>
                    <input type="text" id="name" wire:model="name" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm">
                    @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-stone-700">Descripció</label>
                    <textarea id="description" wire:model="description" rows="2" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"></textarea>
                </div>

                <div>
                    <label for="ingredients" class="block text-sm font-medium text-stone-700">Ingredients</label>
                    <textarea id="ingredients" wire:model="ingredients" rows="4" placeholder="Un ingredient per línia" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"></textarea>
                </div>

                <div>
                    <label for="instructions" class="block text-sm font-medium text-stone-700">Instruccions</label>
                    <textarea id="instructions" wire:model="instructions" rows="4" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" wire:click="cancel" class="px-4 py-2 text-sm font-medium text-stone-700 hover:bg-stone-100 rounded-lg transition-colors">
                        Cancel·lar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors">
                        {{ $editingId ? 'Actualitzar' : 'Crear' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Llista de receptes -->
    <div class="bg-white border border-stone-200 rounded-lg overflow-hidden">
        @if($recipes->isEmpty())
            <div class="p-8 text-center text-stone-500">
                <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                No hi ha receptes. Crea la primera!
            </div>
        @else
            <ul class="divide-y divide-stone-200">
                @foreach($recipes as $recipe)
                    <li class="p-4 hover:bg-stone-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-stone-800">{{ $recipe->name }}</h4>
                                @if($recipe->description)
                                    <p class="text-sm text-stone-500 truncate mt-0.5">{{ $recipe->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-1 ml-4">
                                <button wire:click="edit({{ $recipe->id }})" class="p-2 text-stone-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $recipe->id }})" wire:confirm="Segur que vols eliminar aquesta recepta?" class="p-2 text-stone-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
