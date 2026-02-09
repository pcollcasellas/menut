<div x-data="{
    touchStartX: 0,
    touchStartY: 0,
    slideOffset: 0,
    isSwiping: false,
    SWIPE_THRESHOLD: 60,
    maxDrag: 80,
    mounted() {
        if (window.innerWidth < 768) {
            const today = document.getElementById('day-{{ now()->format('Y-m-d') }}');
            if (today) {
                setTimeout(() => today.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
            }
        }
    },
    onTouchStart(e) {
        this.touchStartX = e.touches[0].clientX;
        this.touchStartY = e.touches[0].clientY;
        this.isSwiping = true;
        this.slideOffset = 0;
    },
    onTouchMove(e) {
        if (!this.isSwiping) return;
        const deltaX = e.touches[0].clientX - this.touchStartX;
        const deltaY = e.touches[0].clientY - this.touchStartY;
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 8) {
            e.preventDefault();
            this.slideOffset = Math.max(-this.maxDrag, Math.min(this.maxDrag, deltaX));
        }
    },
    onTouchEnd(e) {
        if (!this.isSwiping) return;
        this.isSwiping = false;
        const deltaX = e.changedTouches[0].clientX - this.touchStartX;
        const deltaY = e.changedTouches[0].clientY - this.touchStartY;
        if (Math.abs(deltaX) > this.SWIPE_THRESHOLD && Math.abs(deltaX) > Math.abs(deltaY)) {
            if (deltaX > 0) {
                $wire.previousWeek();
            } else {
                $wire.nextWeek();
            }
        }
        this.slideOffset = 0;
    }
}" x-init="mounted()" @touchstart="onTouchStart($event)" @touchmove="onTouchMove($event)" @touchend="onTouchEnd($event)" @touchcancel="isSwiping = false; slideOffset = 0">
    <div class="lg:overflow-visible overflow-x-hidden relative touch-pan-y">
        {{-- Swipe feedback: subtle bg visible in gap when dragging --}}
        <div class="lg:hidden absolute inset-0 bg-stone-100 -z-0" aria-hidden="true"></div>
        <div class="relative z-10 bg-white lg:bg-transparent will-change-transform"
             :style="`transform: translateX(${slideOffset}px); transition: transform ${isSwiping ? '0ms' : '200ms'} cubic-bezier(0.25, 0.1, 0.25, 1)`">
            <!-- Header -->
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
                    <flux:button wire:click="openTemplates" variant="outline" icon="document-duplicate" size="sm">Plantilles</flux:button>
                    <flux:button wire:click="goToToday" variant="outline" size="sm">Avui</flux:button>
                </div>
            </div>

            <!-- Mobile: Vertical card layout -->
            <div class="flex flex-col gap-4 lg:hidden">
                @foreach($weekDays as $day)
                    <div id="day-{{ $day->format('Y-m-d') }}" class="scroll-mt-20 bg-white border border-stone-200 rounded-lg overflow-visible {{ $day->isToday() ? 'ring-2 ring-forest-200' : '' }}">
                        <div class="px-4 py-3 bg-stone-50 border-b border-stone-200 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium text-stone-500 uppercase tracking-wide">{{ $day->translatedFormat('D') }}</div>
                                <div class="text-lg font-semibold {{ $day->isToday() ? 'text-emerald-600' : 'text-stone-700' }}">{{ $day->translatedFormat('j F') }}</div>
                            </div>
                            @if($day->isToday())
                                <span class="px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full">Avui</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-{{ count($this->mealTypes) }} divide-x divide-stone-200 overflow-visible">
                            @foreach($this->mealTypes as $mealType)
                                <div class="p-4 overflow-visible {{ $day->isToday() ? 'bg-emerald-50/50' : '' }}">
                                    <flux:text size="xs" class="font-medium text-stone-500 mb-2 uppercase tracking-wide">{{ $mealType->label() }}</flux:text>
                                    <livewire:meal-slot
                                        :date="$day->format('Y-m-d')"
                                        :mealType="$mealType->value"
                                        :key="'mobile-'.$mealType->value.'-'.$day->format('Y-m-d')"
                                    />
                                </div>
                            @endforeach
                        </div>
                        <div id="day-dropdown-{{ $day->format('Y-m-d') }}" class="mt-2 lg:hidden"></div>
                    </div>
                @endforeach
            </div>

            <!-- Desktop: 7-column grid layout -->
            <div class="hidden lg:block">
                <div class="grid grid-cols-7 gap-3 min-w-[700px]">
                    @foreach($weekDays as $day)
                        <div class="p-2 text-center">
                            <div class="text-xs font-medium text-stone-500 uppercase tracking-wide">{{ $day->translatedFormat('D') }}</div>
                            <div class="text-lg font-semibold {{ $day->isToday() ? 'text-emerald-600' : 'text-stone-700' }}">{{ $day->format('d') }}</div>
                        </div>
                    @endforeach
                    @foreach($this->mealTypes as $mealType)
                        @foreach($weekDays as $day)
                            <div class="min-h-[100px] overflow-visible {{ $day->isToday() ? 'bg-emerald-50 ring-2 ring-emerald-200' : 'bg-white' }} border border-stone-200 rounded-lg p-3">
                                <flux:text size="xs" class="font-medium text-stone-500 mb-2 uppercase tracking-wide">{{ $mealType->label() }}</flux:text>
                                <livewire:meal-slot
                                    :date="$day->format('Y-m-d')"
                                    :mealType="$mealType->value"
                                    :key="'desktop-'.$mealType->value.'-'.$day->format('Y-m-d')"
                                />
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <livewire:template-manager />
</div>
