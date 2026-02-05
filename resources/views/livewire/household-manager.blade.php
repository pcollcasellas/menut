<section>
    <header>
        <h2 class="text-lg font-medium text-bark-800">
            Membres de la llar
        </h2>
        <p class="mt-1 text-sm text-bark-600">
            Gestiona els membres de la teva llar. Tots els membres comparteixen receptes, menús i plantilles.
        </p>
    </header>

    <div class="mt-6 space-y-4">
        {{-- Member list --}}
        <div class="divide-y divide-cream-200 border border-cream-200 rounded-xl overflow-hidden">
            @foreach ($members as $member)
                <div class="flex items-center justify-between px-4 py-3 bg-white">
                    <div>
                        <div class="text-sm font-medium text-bark-800">{{ $member->name }}</div>
                        <div class="text-sm text-bark-500">{{ $member->email }}</div>
                    </div>
                    @if ($member->id === auth()->id())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-forest-100 text-forest-800">
                            Tu
                        </span>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Invite section --}}
        <div class="pt-4 border-t border-cream-200">
            <h3 class="text-sm font-medium text-bark-800">Convidar a la llar</h3>
            <p class="mt-1 text-sm text-bark-500">
                Genera un enllaç d'invitació per afegir una altra persona a la teva llar.
            </p>

            @if ($inviteLink)
                <div class="mt-3 space-y-3">
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            readonly
                            value="{{ $inviteLink }}"
                            class="flex-1 text-sm border-cream-300 rounded-xl bg-cream-50 text-bark-600 focus:border-forest-500 focus:ring-forest-500"
                            x-ref="inviteInput"
                        >
                        <button
                            type="button"
                            x-data="{ copied: false }"
                            x-on:click="
                                navigator.clipboard.writeText($refs.inviteInput.value);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-forest-700 bg-forest-50 border border-forest-200 rounded-xl hover:bg-forest-100 transition-colors"
                        >
                            <span x-show="!copied">Copiar</span>
                            <span x-show="copied" x-cloak>Copiat!</span>
                        </button>
                    </div>
                    @if ($pendingInvitation)
                        <p class="text-xs text-bark-500">
                            Expira {{ $pendingInvitation->expires_at->diffForHumans() }}
                        </p>
                    @endif
                    <button
                        type="button"
                        wire:click="cancelInvitation"
                        class="text-sm text-red-600 hover:text-red-800 transition-colors"
                    >
                        Cancel·lar invitació
                    </button>
                </div>
            @else
                <div class="mt-3">
                    <x-primary-button wire:click="generateInviteLink" type="button">
                        Generar enllaç d'invitació
                    </x-primary-button>
                </div>
            @endif
        </div>

        {{-- Leave household --}}
        @if ($memberCount > 1)
            <div class="pt-4 border-t border-cream-200">
                <h3 class="text-sm font-medium text-bark-800">Sortir de la llar</h3>
                <p class="mt-1 text-sm text-bark-500">
                    Si surts de la llar, les receptes, menús i plantilles es quedaran amb la llar actual i se't crearà una nova llar buida.
                </p>
                <div class="mt-3">
                    <x-danger-button
                        wire:click="leaveHousehold"
                        wire:confirm="Estàs segur que vols sortir de la llar? Les dades es quedaran amb la llar actual."
                        type="button"
                    >
                        Sortir de la llar
                    </x-danger-button>
                </div>
            </div>
        @endif
    </div>
</section>
