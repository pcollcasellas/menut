<div>
    @if($showModal)
        <!-- Modal backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40" wire:click="closeModal"></div>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl {{ $mode === 'edit' ? 'max-w-[95vw] sm:max-w-md md:max-w-4xl' : 'max-w-[95vw] sm:max-w-md' }} w-full max-h-[80vh] overflow-hidden" wire:click.stop>
                <!-- Header -->
                <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-stone-800">
                        @if($mode === 'save')
                            Desar plantilla
                        @elseif($mode === 'import')
                            Importar plantilla
                        @elseif($mode === 'edit')
                            Editar plantilla
                        @else
                            Plantilles
                        @endif
                    </h3>
                    <button wire:click="closeModal" class="text-stone-400 hover:text-stone-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    @if($mode === 'list')
                        <!-- Template list -->
                        <div class="space-y-3">
                            <button wire:click="showSaveForm" class="w-full p-3 rounded-lg border-2 border-dashed border-stone-300 hover:border-emerald-400 hover:bg-emerald-50 transition-colors">
                                <span class="text-sm text-stone-600 flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Desar menu actual com a plantilla
                                </span>
                            </button>

                            @if($templates->count() > 0)
                                <hr class="border-stone-200 my-4">
                                <p class="text-xs font-medium text-stone-500 uppercase tracking-wide mb-2">Les teves plantilles</p>
                            @endif

                            @foreach($templates as $template)
                                <div class="flex items-center justify-between p-3 bg-stone-50 rounded-lg hover:bg-stone-100 transition-colors">
                                    <button wire:click="showImportForm({{ $template->id }})" class="flex-1 text-left">
                                        <span class="text-sm font-medium text-stone-800 block">{{ $template->name }}</span>
                                        <span class="text-xs text-stone-500">{{ $template->items_count }} receptes</span>
                                    </button>
                                    <div class="flex items-center">
                                        <button wire:click="showEditForm({{ $template->id }})" class="p-2 text-stone-400 hover:text-emerald-600 transition-colors" title="Editar plantilla">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="deleteTemplate({{ $template->id }})" wire:confirm="Segur que vols eliminar aquesta plantilla?" class="p-2 text-stone-400 hover:text-red-600 transition-colors" title="Eliminar plantilla">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            @if($templates->count() === 0)
                                <p class="text-sm text-stone-500 text-center py-4">
                                    Encara no tens plantilles desades.
                                </p>
                            @endif
                        </div>

                    @elseif($mode === 'save')
                        <!-- Save form -->
                        <form wire:submit="saveTemplate">
                            <div class="mb-4">
                                <label for="templateName" class="block text-sm font-medium text-stone-700 mb-1">
                                    Nom de la plantilla
                                </label>
                                <input
                                    type="text"
                                    id="templateName"
                                    wire:model="templateName"
                                    class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                    placeholder="Ex: Menu setmanal habitual"
                                    autofocus
                                >
                                @error('templateName')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex gap-3">
                                <button type="button" wire:click="backToList" class="flex-1 px-4 py-2 text-sm font-medium text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors">
                                    Enrere
                                </button>
                                <button type="submit" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                                    Desar
                                </button>
                            </div>
                        </form>

                    @elseif($mode === 'import')
                        <!-- Import form -->
                        <div class="space-y-4">
                            @if($selectedTemplate)
                                <div class="p-3 bg-emerald-50 rounded-lg">
                                    <p class="text-sm font-medium text-emerald-800">{{ $selectedTemplate->name }}</p>
                                    <p class="text-xs text-emerald-600">{{ $selectedTemplate->items->count() }} receptes</p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-stone-700 mb-2">Mode d'importacio</p>
                                    <div class="space-y-2">
                                        <label class="flex items-start gap-3 p-3 rounded-lg border border-stone-200 cursor-pointer hover:bg-stone-50 transition-colors">
                                            <input type="radio" wire:model="importMode" value="skip" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                            <div>
                                                <span class="text-sm font-medium text-stone-800 block">Saltar existents</span>
                                                <span class="text-xs text-stone-500">Nomes omple les caselles buides</span>
                                            </div>
                                        </label>
                                        <label class="flex items-start gap-3 p-3 rounded-lg border border-stone-200 cursor-pointer hover:bg-stone-50 transition-colors">
                                            <input type="radio" wire:model="importMode" value="replace" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                            <div>
                                                <span class="text-sm font-medium text-stone-800 block">Substituir tot</span>
                                                <span class="text-xs text-stone-500">Sobreescriu tot el menu de la setmana</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="flex gap-3">
                                    <button type="button" wire:click="backToList" class="flex-1 px-4 py-2 text-sm font-medium text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors">
                                        Enrere
                                    </button>
                                    <button type="button" wire:click="importTemplate" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                                        Importar
                                    </button>
                                </div>
                            @endif
                        </div>

                    @elseif($mode === 'edit')
                        <!-- Edit form -->
                        <div class="space-y-4">
                            <!-- Template name input -->
                            <div>
                                <label for="editingTemplateName" class="block text-sm font-medium text-stone-700 mb-1">
                                    Nom de la plantilla
                                </label>
                                <input
                                    type="text"
                                    id="editingTemplateName"
                                    wire:model="editingTemplateName"
                                    class="w-full px-3 py-2 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                                >
                                @error('editingTemplateName')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Grid editor -->
                            @php
                                $days = ['Dl', 'Dt', 'Dc', 'Dj', 'Dv', 'Ds', 'Dg'];
                                $mealTypes = ['lunch' => 'Dinar', 'dinner' => 'Sopar'];
                            @endphp

                            <div class="overflow-x-auto -mx-6 px-6 sm:mx-0 sm:px-0">
                                <table class="w-full border-collapse min-w-[600px] sm:min-w-0">
                                    <thead>
                                        <tr>
                                            <th class="p-2 text-xs font-medium text-stone-500 text-left w-16"></th>
                                            @foreach($days as $day)
                                                <th class="p-2 text-xs font-medium text-stone-600 text-center">{{ $day }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mealTypes as $mealType => $mealLabel)
                                            <tr>
                                                <td class="p-2 text-xs font-medium text-stone-600">{{ $mealLabel }}</td>
                                                @for($day = 0; $day < 7; $day++)
                                                    @php
                                                        $slotKey = "{$day}_{$mealType}";
                                                        $recipeId = $editingSlots[$slotKey] ?? null;
                                                        $recipe = $recipeId ? $recipes->firstWhere('id', $recipeId) : null;
                                                    @endphp
                                                    <td class="p-1 relative">
                                                        <div class="relative">
                                                            <button
                                                                wire:click="toggleSlotSelector('{{ $slotKey }}')"
                                                                class="w-full min-h-[56px] sm:min-h-[60px] p-2 rounded-lg border {{ $recipe ? 'bg-emerald-50 border-emerald-200 hover:bg-emerald-100' : 'bg-stone-50 border-stone-200 hover:bg-stone-100 border-dashed' }} transition-colors text-left"
                                                            >
                                                                @if($recipe)
                                                                    <span class="text-xs font-medium text-emerald-800 line-clamp-2">{{ $recipe->name }}</span>
                                                                @else
                                                                    <span class="text-xs text-stone-400 flex items-center justify-center h-full">
                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                                        </svg>
                                                                    </span>
                                                                @endif
                                                            </button>

                                                            <!-- Dropdown selector -->
                                                            @if($activeSlot === $slotKey)
                                                                <div class="absolute z-10 mt-1 w-48 bg-white rounded-lg shadow-lg border border-stone-200 max-h-48 overflow-y-auto left-0 sm:left-auto">
                                                                    @if($recipe)
                                                                        <button
                                                                            wire:click="updateSlot('{{ $slotKey }}', null)"
                                                                            class="w-full px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50 transition-colors border-b border-stone-100"
                                                                        >
                                                                            Treure recepta
                                                                        </button>
                                                                    @endif
                                                                    @foreach($recipes as $r)
                                                                        <button
                                                                            wire:click="updateSlot('{{ $slotKey }}', {{ $r->id }})"
                                                                            class="w-full px-3 py-2 text-left text-xs hover:bg-stone-50 transition-colors {{ $r->id === $recipeId ? 'bg-emerald-50 text-emerald-800' : 'text-stone-700' }}"
                                                                        >
                                                                            {{ $r->name }}
                                                                        </button>
                                                                    @endforeach
                                                                    @if($recipes->isEmpty())
                                                                        <p class="px-3 py-2 text-xs text-stone-500">No tens receptes</p>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button type="button" wire:click="backToList" class="flex-1 px-4 py-2 text-sm font-medium text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors">
                                    Enrere
                                </button>
                                <button type="button" wire:click="saveTemplateChanges" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                                    Desar canvis
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
