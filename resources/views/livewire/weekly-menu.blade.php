<div x-data="{
    mounted() {
        if (window.innerWidth < 768) {
            const today = document.getElementById('day-{{ now()->format('Y-m-d') }}');
            if (today) {
                setTimeout(() => {
                    today.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            }
        }
    }
}" x-init="mounted()">
    <!-- Header amb navegació de setmanes -->
    <div class="sticky top-16 z-20 bg-white pb-4 pt-2 -mt-2 flex flex-col sm:flex-row items-center justify-between mb-6 gap-3 sm:gap-0">
        <button wire:click="previousWeek" class="p-3 w-12 h-12 sm:p-2 sm:w-auto sm:h-auto text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors">
            <svg class="w-6 h-6 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <div class="text-center">
            <h2 class="text-base md:text-lg font-semibold text-stone-800">
                Setmana del {{ \Carbon\Carbon::parse($currentWeekStart)->translatedFormat('j F') }} - {{ \Carbon\Carbon::parse($currentWeekStart)->addDays(6)->translatedFormat('j F, Y') }}
            </h2>
        </div>

        <div class="flex gap-2">
            <button wire:click="openTemplates" class="px-3 py-2 sm:py-1.5 text-sm font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                </svg>
                Plantilles
            </button>
            <button wire:click="goToToday" class="px-3 py-2 sm:py-1.5 text-sm font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors">
                Avui
            </button>
            <button wire:click="nextWeek" class="p-3 w-12 h-12 sm:p-2 sm:w-auto sm:h-auto text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition-colors">
                <svg class="w-6 h-6 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile: Vertical card layout -->
    <div class="flex flex-col gap-4 lg:hidden">
        @foreach($weekDays as $day)
            <div id="day-{{ $day->format('Y-m-d') }}" class="bg-white border border-stone-200 rounded-lg overflow-visible {{ $day->isToday() ? 'ring-2 ring-forest-200' : '' }}">
                <!-- Day header -->
                <div class="px-4 py-3 bg-stone-50 border-b border-stone-200 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-medium text-stone-500 uppercase tracking-wide">
                            {{ $day->translatedFormat('D') }}
                        </div>
                        <div class="text-lg font-semibold {{ $day->isToday() ? 'text-emerald-600' : 'text-stone-700' }}">
                            {{ $day->translatedFormat('j F') }}
                        </div>
                    </div>
                    @if($day->isToday())
                        <span class="px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full">Avui</span>
                    @endif
                </div>

                <!-- Meals grid (lunch | dinner) -->
                <div class="grid grid-cols-2 divide-x divide-stone-200 overflow-visible">
                    <!-- Lunch -->
                    <div class="p-4 overflow-visible {{ $day->isToday() ? 'bg-emerald-50/50' : '' }}">
                        <div class="text-xs font-medium text-stone-500 mb-2 uppercase tracking-wide">Dinar</div>
                        <livewire:meal-slot
                            :date="$day->format('Y-m-d')"
                            :mealType="'lunch'"
                            :key="'lunch-'.$day->format('Y-m-d')"
                        />
                    </div>

                    <!-- Dinner -->
                    <div class="p-4 overflow-visible {{ $day->isToday() ? 'bg-emerald-50/50' : '' }}">
                        <div class="text-xs font-medium text-stone-500 mb-2 uppercase tracking-wide">Sopar</div>
                        <livewire:meal-slot
                            :date="$day->format('Y-m-d')"
                            :mealType="'dinner'"
                            :key="'dinner-'.$day->format('Y-m-d')"
                        />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Desktop: Original 7-column grid layout -->
    <div class="hidden lg:block">
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
                <div class="min-h-[100px] overflow-visible {{ $day->isToday() ? 'bg-emerald-50 ring-2 ring-emerald-200' : 'bg-white' }} border border-stone-200 rounded-lg p-3">
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
                <div class="min-h-[100px] overflow-visible {{ $day->isToday() ? 'bg-emerald-50 ring-2 ring-emerald-200' : 'bg-white' }} border border-stone-200 rounded-lg p-3">
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
