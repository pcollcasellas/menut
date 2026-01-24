<div>
    <!-- Header amb navegació de setmanes -->
    <div class="flex items-center justify-between mb-6">
        <button wire:click="previousWeek" class="p-2 text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="text-center">
            <h2 class="text-lg font-semibold text-stone-800">
                Setmana del {{ \Carbon\Carbon::parse($currentWeekStart)->translatedFormat('j F') }} - {{ \Carbon\Carbon::parse($currentWeekStart)->addDays(6)->translatedFormat('j F, Y') }}
            </h2>
        </div>

        <div class="flex gap-2">
            <button wire:click="openTemplates" class="px-3 py-1.5 text-sm font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                </svg>
                Plantilles
            </button>
            <button wire:click="goToToday" class="px-3 py-1.5 text-sm font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors">
                Avui
            </button>
            <button wire:click="nextWeek" class="p-2 text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Graella del menú setmanal -->
    <div class="overflow-x-auto">
        <div class="grid grid-cols-7 gap-3 min-w-[700px]">
            <!-- Capçaleres dels dies -->
            @foreach($weekDays as $day)
                <div class="p-2 text-center">
                    <div class="text-xs font-medium text-stone-500 uppercase tracking-wide">
                        {{ $day->translatedFormat('D') }}
                    </div>
                    <div class="text-lg font-semibold {{ $day->isToday() ? 'text-emerald-600' : 'text-stone-700' }}">
                        {{ $day->format('d') }}
                    </div>
                </div>
            @endforeach

            <!-- Files de Dinar -->
            @foreach($weekDays as $day)
                <div class="min-h-[100px] {{ $day->isToday() ? 'bg-emerald-50 ring-2 ring-emerald-200' : 'bg-white' }} border border-stone-200 rounded-lg p-3">
                    <div class="text-xs font-medium text-stone-500 mb-2 uppercase tracking-wide">Dinar</div>
                    <livewire:meal-slot
                        :date="$day->format('Y-m-d')"
                        :mealType="'lunch'"
                        :key="'lunch-'.$day->format('Y-m-d')"
                    />
                </div>
            @endforeach

            <!-- Files de Sopar -->
            @foreach($weekDays as $day)
                <div class="min-h-[100px] {{ $day->isToday() ? 'bg-emerald-50 ring-2 ring-emerald-200' : 'bg-white' }} border border-stone-200 rounded-lg p-3">
                    <div class="text-xs font-medium text-stone-500 mb-2 uppercase tracking-wide">Sopar</div>
                    <livewire:meal-slot
                        :date="$day->format('Y-m-d')"
                        :mealType="'dinner'"
                        :key="'dinner-'.$day->format('Y-m-d')"
                    />
                </div>
            @endforeach
        </div>
    </div>

    <livewire:template-manager />
</div>
