<div
    class="relative"
    x-data="{ ownerId: '{{ $this->getId() }}' }"
    @click.window="
        if (!$wire.showSelector) return;
        if ($el.contains($event.target)) return;
        if ($event.target.closest && $event.target.closest('[data-dropdown-owner=\'' + ownerId + '\']')) return;
        $wire.toggleSelector();
    "
    @keydown.escape.window="if ($wire.showSelector) $wire.toggleSelector()"
>
    @if($currentRecipe)
        <!-- Recepta assignada -->
        <div class="flex items-center min-h-[44px] rounded-lg bg-emerald-100">
            <button wire:click="toggleSelector" class="flex-1 text-left p-3 md:p-2 hover:bg-emerald-200 rounded-l-lg transition-colors">
                <span class="text-sm md:text-xs font-medium text-emerald-800 block">
                    {{ $currentRecipe->name }}
                </span>
            </button>
            <flux:modal.trigger name="preview-recipe-{{ $this->getId() }}">
                <button class="self-stretch px-2 flex items-center text-emerald-600 hover:text-emerald-800 hover:bg-emerald-200 rounded-r-lg transition-colors" title="Veure recepta">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </flux:modal.trigger>
        </div>

        <!-- Modal de previsualització -->
        <flux:modal name="preview-recipe-{{ $this->getId() }}" class="md:w-96 lg:w-[32rem]">
            <div class="space-y-4">
                <flux:heading size="lg">{{ $currentRecipe->name }}</flux:heading>

                @if($currentRecipe->description)
                    <div>
                        <flux:text size="xs" class="font-semibold text-stone-500 uppercase tracking-wide">Descripció</flux:text>
                        <flux:text class="mt-1">{{ $currentRecipe->description }}</flux:text>
                    </div>
                @endif

                @if($currentRecipe->ingredientItems && $currentRecipe->ingredientItems->count() > 0)
                    <div>
                        <flux:text size="xs" class="font-semibold text-stone-500 uppercase tracking-wide">Ingredients</flux:text>
                        <div class="flex flex-wrap gap-1 mt-2">
                            @foreach($currentRecipe->ingredientItems as $ingredient)
                                <span class="inline-flex px-2 py-1 bg-cream-100 text-bark-600 rounded-full text-sm">
                                    {{ $ingredient->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($currentRecipe->instructions)
                    <div>
                        <flux:text size="xs" class="font-semibold text-stone-500 uppercase tracking-wide">Instruccions</flux:text>
                        <pre class="mt-1 text-xs text-stone-600 font-sans whitespace-pre-wrap">{{ $currentRecipe->instructions }}</pre>
                    </div>
                @endif
            </div>
        </flux:modal>
    @else
        <!-- Botó per afegir recepta -->
        <button wire:click="toggleSelector" class="w-full p-3 md:p-2 min-h-[44px] rounded-lg border-2 border-dashed border-stone-300 hover:border-emerald-400 hover:bg-emerald-50 transition-colors">
            <span class="text-sm md:text-xs text-stone-400 flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Afegir
            </span>
        </button>
    @endif

    <!-- Selector de receptes -->
    @if($showSelector)
        <template x-teleport="#day-dropdown-{{ $date }}">
            <div class="lg:hidden w-full bg-white rounded-lg shadow-lg border border-stone-200" data-dropdown-owner="{{ $this->getId() }}">
                <!-- Barra de cerca -->
                <div class="p-2 border-b border-stone-200" x-data x-init="$nextTick(() => { const input = $el.querySelector('input'); if (input) { input.focus(); input.click(); } })">
                    <flux:input
                        icon="magnifying-glass"
                        wire:model.live="searchQuery"
                        placeholder="Cerca receptes..."
                        size="sm"
                        inputmode="search"
                        autofocus
                    />
                </div>

                <div class="py-1 max-h-60 overflow-y-auto">
                    @if($currentRecipe)
                        <button wire:click="selectRecipe(null)" class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            Treure recepta
                        </button>
                        <hr class="border-stone-200">
                    @endif

                    @forelse($recipes as $recipe)
                        <button wire:click="selectRecipe({{ $recipe->id }})" class="w-full text-left px-4 py-3 text-sm text-stone-700 hover:bg-stone-100 transition-colors {{ $recipe->id === $selectedRecipeId ? 'bg-emerald-50 text-emerald-700' : '' }}">
                            {{ $recipe->name }}
                        </button>
                    @empty
                        <div class="px-4 py-3 text-sm text-stone-500">
                            @if(trim($searchQuery) !== '')
                                No s'han trobat receptes
                            @else
                                No hi ha receptes
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </template>

        <div class="hidden lg:block absolute z-50 mt-1 w-48 left-0 bg-white rounded-lg shadow-lg border border-stone-200" data-dropdown-owner="{{ $this->getId() }}">
            <!-- Barra de cerca -->
            <div class="p-2 border-b border-stone-200" x-data x-init="$nextTick(() => { const input = $el.querySelector('input'); if (input) { input.focus(); input.click(); } })">
                <flux:input
                    icon="magnifying-glass"
                    wire:model.live="searchQuery"
                    placeholder="Cerca receptes..."
                    size="sm"
                    inputmode="search"
                    autofocus
                />
            </div>

            <div class="py-1 max-h-48 overflow-y-auto">
                @if($currentRecipe)
                    <button wire:click="selectRecipe(null)" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        Treure recepta
                    </button>
                    <hr class="border-stone-200">
                @endif

                @forelse($recipes as $recipe)
                    <button wire:click="selectRecipe({{ $recipe->id }})" class="w-full text-left px-4 py-2 text-sm text-stone-700 hover:bg-stone-100 transition-colors {{ $recipe->id === $selectedRecipeId ? 'bg-emerald-50 text-emerald-700' : '' }}">
                        {{ $recipe->name }}
                    </button>
                @empty
                    <div class="px-4 py-2 text-sm text-stone-500">
                        @if(trim($searchQuery) !== '')
                            No s'han trobat receptes
                        @else
                            No hi ha receptes
                        @endif
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
