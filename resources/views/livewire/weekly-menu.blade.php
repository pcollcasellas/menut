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
    <div class="lg:sticky lg:top-16 z-20 bg-white pb-4 pt-2 -mt-2 mb-6">
        <div class="grid grid-cols-[auto_1fr_auto] items-center gap-2 sm:flex sm:gap-0 sm:items-center sm:justify-between">
            <flux:button wire:click="previousWeek" variant="ghost" icon="chevron-left" square class="w-12 h-12 sm:w-auto sm:h-auto" />

            <div class="text-center px-2 sm:px-0">
                <flux:heading size="lg" class="text-base md:text-lg">
                    Setmana del {{ \Carbon\Carbon::parse($currentWeekStart)->translatedFormat('j F') }} - {{ \Carbon\Carbon::parse($currentWeekStart)->addDays(6)->translatedFormat('j F, Y') }}
                </flux:heading>
            </div>

            <flux:button wire:click="nextWeek" variant="ghost" icon="chevron-right" square class="w-12 h-12 sm:w-auto sm:h-auto" />
        </div>

        <div class="mt-3 flex justify-center gap-2 sm:mt-0 sm:justify-end">
            <flux:button wire:click="openTemplates" variant="outline" icon="document-duplicate" size="sm">
                Plantilles
            </flux:button>
            <flux:button wire:click="goToToday" variant="outline" size="sm">
                Avui
            </flux:button>
        </div>
    </div>

    <!-- Mobile: Vertical card layout -->
    <div class="flex flex-col gap-4 lg:hidden">
        @foreach($weekDays as $day)
            <div id="day-{{ $day->format('Y-m-d') }}" class="scroll-mt-20 bg-white border border-stone-200 rounded-lg overflow-visible {{ $day->isToday() ? 'ring-2 ring-forest-200' : '' }}">
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
                        <flux:text size="xs" class="font-medium text-stone-500 mb-2 uppercase tracking-wide">Dinar</flux:text>
                        <livewire:meal-slot
                            :date="$day->format('Y-m-d')"
                            :mealType="'lunch'"
                            :key="'mobile-lunch-'.$day->format('Y-m-d')"
                        />
                    </div>

                    <!-- Dinner -->
                    <div class="p-4 overflow-visible {{ $day->isToday() ? 'bg-emerald-50/50' : '' }}">
                        <flux:text size="xs" class="font-medium text-stone-500 mb-2 uppercase tracking-wide">Sopar</flux:text>
                        <livewire:meal-slot
                            :date="$day->format('Y-m-d')"
                            :mealType="'dinner'"
                            :key="'mobile-dinner-'.$day->format('Y-m-d')"
                        />
                    </div>
                </div>

                <div id="day-dropdown-{{ $day->format('Y-m-d') }}" class="mt-2 lg:hidden"></div>
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
                    <flux:text size="xs" class="font-medium text-stone-500 mb-2 uppercase tracking-wide">Dinar</flux:text>
                    <livewire:meal-slot
                        :date="$day->format('Y-m-d')"
                        :mealType="'lunch'"
                        :key="'desktop-lunch-'.$day->format('Y-m-d')"
                    />
                </div>
            @endforeach

            <!-- Files de Sopar -->
            @foreach($weekDays as $day)
                <div class="min-h-[100px] overflow-visible {{ $day->isToday() ? 'bg-emerald-50 ring-2 ring-emerald-200' : 'bg-white' }} border border-stone-200 rounded-lg p-3">
                    <flux:text size="xs" class="font-medium text-stone-500 mb-2 uppercase tracking-wide">Sopar</flux:text>
                    <livewire:meal-slot
                        :date="$day->format('Y-m-d')"
                        :mealType="'dinner'"
                        :key="'desktop-dinner-'.$day->format('Y-m-d')"
                    />
                </div>
            @endforeach
        </div>
    </div>

    <livewire:template-manager />
</div>
