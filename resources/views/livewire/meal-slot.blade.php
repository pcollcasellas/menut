<div
    class="relative"
    x-data="{
        ownerId: '{{ $this->getId() }}',
        closeIfOutside(event) {
            const target = event?.target;
            if (target && target.closest && target.closest('[data-dropdown-owner=\"' + this.ownerId + '\"]')) {
                return;
            }

            if ($wire.showSelector) {
                $wire.toggleSelector();
            }
        }
    }"
    @click.outside="closeIfOutside($event)"
>
    @if($currentRecipe)
        <!-- Recepta assignada -->
        <button wire:click="toggleSelector" class="w-full text-left p-3 md:p-2 min-h-[44px] rounded-lg bg-emerald-100 hover:bg-emerald-200 transition-colors">
            <span class="text-sm md:text-xs font-medium text-emerald-800 block truncate">
                {{ $currentRecipe->name }}
            </span>
        </button>
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
