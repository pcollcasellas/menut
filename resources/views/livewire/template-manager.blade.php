<div>
    @if($showModal)
        <!-- Modal backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 z-40" wire:click="closeModal"></div>

        <!-- Modal -->
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl {{ $mode === 'edit' ? 'max-w-[95vw] sm:max-w-md md:max-w-4xl' : 'max-w-[95vw] sm:max-w-md' }} w-full max-h-[80vh] overflow-hidden" wire:click.stop>
                <!-- Header -->
                <div class="px-6 py-4 border-b border-stone-200 flex items-center justify-between">
                    <flux:heading size="lg">
                        @if($mode === 'save')
                            Desar plantilla
                        @elseif($mode === 'import')
                            Importar plantilla
                        @elseif($mode === 'edit')
                            Editar plantilla
                        @else
                            Plantilles
                        @endif
                    </flux:heading>
                    <flux:button wire:click="closeModal" variant="ghost" icon="x-mark" square size="sm" />
                </div>

                <!-- Content -->
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    @if($mode === 'list')
                        <!-- Template list -->
                        <div class="space-y-3">
                            <flux:button wire:click="showSaveForm" variant="outline" icon="plus" class="w-full border-2 border-dashed border-stone-300 hover:border-emerald-400 hover:bg-emerald-50">
                                Desar menu actual com a plantilla
                            </flux:button>

                            @if($templates->count() > 0)
                                <hr class="border-stone-200 my-4">
                                <flux:text size="xs" class="font-medium text-stone-500 uppercase tracking-wide mb-2">Les teves plantilles</flux:text>
                            @endif

                            @foreach($templates as $template)
                                <div class="flex items-center justify-between p-3 bg-stone-50 rounded-lg hover:bg-stone-100 transition-colors">
                                    <button wire:click="showImportForm({{ $template->id }})" class="flex-1 text-left">
                                        <flux:text class="font-medium text-stone-800 block">{{ $template->name }}</flux:text>
                                        <flux:text size="xs" class="text-stone-500">{{ $template->items_count }} receptes</flux:text>
                                    </button>
                                    <div class="flex items-center">
                                        <flux:button wire:click="showEditForm({{ $template->id }})" variant="ghost" icon="pencil" square size="sm" title="Editar plantilla" />
                                        <flux:button
                                            wire:click="deleteTemplate({{ $template->id }})"
                                            wire:confirm="Segur que vols eliminar aquesta plantilla?"
                                            variant="ghost"
                                            icon="trash"
                                            square
                                            size="sm"
                                            class="text-stone-400 hover:text-red-600"
                                            title="Eliminar plantilla"
                                        />
                                    </div>
                                </div>
                            @endforeach

                            @if($templates->count() === 0)
                                <flux:text size="sm" class="text-stone-500 text-center py-4 block">
                                    Encara no tens plantilles desades.
                                </flux:text>
                            @endif
                        </div>

                    @elseif($mode === 'save')
                        <!-- Save form -->
                        <form wire:submit="saveTemplate">
                            <div class="mb-4">
                                <flux:input
                                    wire:model="templateName"
                                    label="Nom de la plantilla"
                                    name="templateName"
                                    placeholder="Ex: Menu setmanal habitual"
                                />
                                <flux:error name="templateName" />
                            </div>

                            <div class="flex gap-3">
                                <flux:button type="button" wire:click="backToList" variant="ghost" class="flex-1">
                                    Enrere
                                </flux:button>
                                <flux:button type="submit" variant="primary" class="flex-1">
                                    Desar
                                </flux:button>
                            </div>
                        </form>

                    @elseif($mode === 'import')
                        <!-- Import form -->
                        <div class="space-y-4">
                            @if($selectedTemplate)
                                <div class="p-3 bg-emerald-50 rounded-lg">
                                    <flux:text class="font-medium text-emerald-800">{{ $selectedTemplate->name }}</flux:text>
                                    <flux:text size="xs" class="text-emerald-600">{{ $selectedTemplate->items->count() }} receptes</flux:text>
                                </div>

                                <div>
                                    <flux:text class="font-medium text-stone-700 mb-2">Mode d'importació</flux:text>
                                    <flux:radio.group wire:model="importMode" class="space-y-2">
                                        <flux:radio value="skip" class="p-3 rounded-lg border border-stone-200 cursor-pointer hover:bg-stone-50">
                                            <div>
                                                <flux:text class="font-medium text-stone-800 block">Saltar existents</flux:text>
                                                <flux:text size="xs" class="text-stone-500">Només omple les caselles buides</flux:text>
                                            </div>
                                        </flux:radio>
                                        <flux:radio value="replace" class="p-3 rounded-lg border border-stone-200 cursor-pointer hover:bg-stone-50">
                                            <div>
                                                <flux:text class="font-medium text-stone-800 block">Substituir tot</flux:text>
                                                <flux:text size="xs" class="text-stone-500">Sobreescriu tot el menú de la setmana</flux:text>
                                            </div>
                                        </flux:radio>
                                    </flux:radio.group>
                                </div>

                                <div class="flex gap-3">
                                    <flux:button type="button" wire:click="backToList" variant="ghost" class="flex-1">
                                        Enrere
                                    </flux:button>
                                    <flux:button type="button" wire:click="importTemplate" variant="primary" class="flex-1">
                                        Importar
                                    </flux:button>
                                </div>
                            @endif
                        </div>

                    @elseif($mode === 'edit')
                        <!-- Edit form -->
                        <div class="space-y-4">
                            <!-- Template name input -->
                            <div>
                                <flux:input
                                    wire:model="editingTemplateName"
                                    label="Nom de la plantilla"
                                    name="editingTemplateName"
                                />
                                <flux:error name="editingTemplateName" />
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
                                                                        <flux:button
                                                                            wire:click="updateSlot('{{ $slotKey }}', null)"
                                                                            variant="danger"
                                                                            size="sm"
                                                                            class="w-full justify-start rounded-none"
                                                                        >
                                                                            Treure recepta
                                                                        </flux:button>
                                                                    @endif
                                                                    @foreach($recipes as $r)
                                                                        <flux:button
                                                                            wire:click="updateSlot('{{ $slotKey }}', {{ $r->id }})"
                                                                            variant="ghost"
                                                                            size="sm"
                                                                            class="w-full justify-start rounded-none {{ $r->id === $recipeId ? 'bg-emerald-50 text-emerald-800' : '' }}"
                                                                        >
                                                                            {{ $r->name }}
                                                                        </flux:button>
                                                                    @endforeach
                                                                    @if($recipes->isEmpty())
                                                                        <flux:text size="xs" class="px-3 py-2 text-stone-500">No tens receptes</flux:text>
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
                                <flux:button type="button" wire:click="backToList" variant="ghost" class="flex-1">
                                    Enrere
                                </flux:button>
                                <flux:button type="button" wire:click="saveTemplateChanges" variant="primary" class="flex-1">
                                    Desar canvis
                                </flux:button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
