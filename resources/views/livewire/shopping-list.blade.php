<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <flux:heading size="xl">Llista de la Compra</flux:heading>
        <div class="flex flex-wrap gap-2">
            <flux:button wire:click="toggleAddFromRecipes" variant="outline" icon="calendar-days">
                Afegir des de receptes
            </flux:button>
            @if($this->boughtItems->count() > 0)
                <flux:button
                    wire:click="clearAllBought"
                    wire:confirm="Segur que vols eliminar tots els elements comprats?"
                    variant="ghost"
                    icon="trash"
                >
                    Netejar comprats
                </flux:button>
            @endif
            @if($this->toBuyItems->count() > 0 || $this->boughtItems->count() > 0)
                <flux:button
                    wire:click="clearAll"
                    wire:confirm="Segur que vols eliminar TOTS els elements de la llista?"
                    variant="ghost"
                    icon="trash"
                    class="text-red-600 hover:text-red-700 hover:bg-red-50"
                >
                    Netejar tot
                </flux:button>
            @endif
        </div>
    </div>

    <!-- Add from recipes panel -->
    @if($showAddFromRecipes)
        <div class="bg-white border border-stone-200 rounded-lg p-6 mb-6">
            <flux:heading size="lg" class="mb-4">Afegir ingredients des de receptes</flux:heading>
            <flux:text size="sm" class="text-stone-500 mb-4">
                Selecciona un rang de dates per afegir tots els ingredients de les receptes programades.
            </flux:text>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="space-y-3">
                    <flux:label>Des de</flux:label>
                    <div class="flex gap-2">
                        <input
                            type="date"
                            wire:model="startDate"
                            class="flex-1 px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-forest-500 focus:border-forest-500"
                        />
                        <flux:select wire:model="startMealType" class="w-28">
                            <option value="lunch">Dinar</option>
                            <option value="dinner">Sopar</option>
                        </flux:select>
                    </div>
                </div>
                <div class="space-y-3">
                    <flux:label>Fins a</flux:label>
                    <div class="flex gap-2">
                        <input
                            type="date"
                            wire:model="endDate"
                            class="flex-1 px-3 py-2 border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-forest-500 focus:border-forest-500"
                        />
                        <flux:select wire:model="endMealType" class="w-28">
                            <option value="lunch">Dinar</option>
                            <option value="dinner">Sopar</option>
                        </flux:select>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <flux:button type="button" wire:click="toggleAddFromRecipes" variant="ghost">
                    Cancel·lar
                </flux:button>
                <flux:button type="button" wire:click="addFromRecipes" variant="primary">
                    Afegir ingredients
                </flux:button>
            </div>
        </div>
    @endif

    <!-- Manual add item -->
    <div class="bg-white border border-stone-200 rounded-lg p-4 mb-6">
        <form wire:submit="addItem" class="flex gap-3">
            <flux:input
                wire:model="newItemName"
                placeholder="Afegir un element..."
                class="flex-1"
            />
            <flux:button type="submit" variant="primary" icon="plus">
                Afegir
            </flux:button>
        </form>
    </div>

    <!-- To buy items -->
    <div class="bg-white border border-stone-200 rounded-lg overflow-hidden mb-6">
        <div class="px-4 py-3 bg-forest-50 border-b border-stone-200">
            <flux:heading size="sm" class="text-forest-800">
                Per comprar ({{ $this->toBuyItems->sum('quantity') }} elements)
            </flux:heading>
        </div>

        @if($this->toBuyItems->isEmpty())
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-stone-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
                <flux:text class="text-stone-500">
                    La llista està buida. Afegeix elements manualment o des de les receptes programades!
                </flux:text>
            </div>
        @else
            <ul class="divide-y divide-stone-200">
                @foreach($this->toBuyItems as $item)
                    <li class="p-4 hover:bg-stone-50 transition-colors">
                        <div class="flex items-center gap-3">
                            {{-- Checkbox to mark as bought --}}
                            <button
                                type="button"
                                wire:click="toggleBought({{ $item->id }})"
                                class="flex-shrink-0 w-5 h-5 rounded border-2 border-stone-300 hover:border-forest-500 hover:bg-forest-50 transition-colors focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2"
                                title="Marcar com a comprat"
                            >
                            </button>
                            {{-- Item name --}}
                            <flux:text class="flex-1 text-stone-800">{{ $item->name }}</flux:text>
                            {{-- Quantity controls --}}
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    wire:click="decrementQuantity({{ $item->id }})"
                                    class="w-6 h-6 flex items-center justify-center text-stone-400 hover:text-stone-600 transition-colors focus:outline-none"
                                    title="Reduir quantitat"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span class="w-6 text-center font-medium text-stone-700">{{ $item->quantity }}</span>
                                <button
                                    type="button"
                                    wire:click="incrementQuantity({{ $item->id }})"
                                    class="w-6 h-6 flex items-center justify-center text-stone-400 hover:text-stone-600 transition-colors focus:outline-none"
                                    title="Augmentar quantitat"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Bought items -->
    @if($this->boughtItems->count() > 0)
        <div class="bg-white border border-stone-200 rounded-lg overflow-hidden" x-data="{ expanded: true }">
            <button
                type="button"
                @click="expanded = !expanded"
                class="w-full px-4 py-3 bg-stone-50 border-b border-stone-200 flex items-center justify-between hover:bg-stone-100 transition-colors"
            >
                <flux:heading size="sm" class="text-stone-600">
                    Comprats ({{ $this->boughtItems->sum('quantity') }})
                </flux:heading>
                <svg
                    class="w-5 h-5 text-stone-400 transition-transform"
                    :class="{ 'rotate-180': expanded }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <ul x-show="expanded" x-collapse class="divide-y divide-stone-200">
                @foreach($this->boughtItems as $item)
                    <li class="p-4 hover:bg-stone-50 transition-colors">
                        <div class="flex items-center gap-3">
                            {{-- Checked checkbox --}}
                            <button
                                type="button"
                                wire:click="toggleBought({{ $item->id }})"
                                class="flex-shrink-0 w-5 h-5 rounded border-2 border-forest-600 bg-forest-600 flex items-center justify-center hover:bg-forest-700 hover:border-forest-700 transition-colors focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2"
                                title="Desmarcar"
                            >
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            {{-- Item name --}}
                            <flux:text class="flex-1 text-stone-400">{{ $item->name }}</flux:text>
                            {{-- Quantity display --}}
                            <span class="w-6 text-center font-medium text-stone-400">{{ $item->quantity }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
