<?php

namespace App\Livewire;

use App\Enums\MealType;
use App\Livewire\Concerns\BelongsToHousehold;
use App\Models\MenuItem;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class WeeklyMenu extends Component
{
    use BelongsToHousehold;

    public string $currentWeekStart;

    public function mount(): void
    {
        $this->currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    #[Computed]
    public function showBreakfast(): bool
    {
        return auth()->user()->show_breakfast ?? false;
    }

    #[Computed]
    public function mealTypes(): array
    {
        $types = [];

        if ($this->showBreakfast) {
            $types[] = MealType::Breakfast;
        }

        $types[] = MealType::Lunch;
        $types[] = MealType::Dinner;

        return $types;
    }

    public function previousWeek(): void
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)
            ->subWeek()
            ->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)
            ->addWeek()
            ->format('Y-m-d');
    }

    public function goToToday(): void
    {
        $this->currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    #[On('menu-updated')]
    public function refreshMenu(): void
    {
        // This will trigger a re-render
    }

    public function openTemplates(): void
    {
        $this->dispatch('open-template-manager', weekStart: $this->currentWeekStart);
    }

    public function getWeekDays(): array
    {
        return $this->getWeekDaysFor($this->currentWeekStart);
    }

    /**
     * @return array<int, Carbon>
     */
    private function getWeekDaysFor(string $weekStart): array
    {
        $start = Carbon::parse($weekStart);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $days[] = $start->copy()->addDays($i);
        }

        return $days;
    }

    public function getMenuItems(): array
    {
        $start = Carbon::parse($this->currentWeekStart);
        $end = $start->copy()->addDays(6);

        $items = MenuItem::with('recipe')
            ->where('household_id', $this->householdId())
            ->whereBetween('date', [$start, $end])
            ->get();

        $menuByDay = [];
        foreach ($items as $item) {
            $key = $item->date->format('Y-m-d').'_'.$item->meal_type->value;
            $menuByDay[$key] = $item;
        }

        return $menuByDay;
    }

    public function render()
    {
        return view('livewire.weekly-menu', [
            'weekDays' => $this->getWeekDays(),
            'menuItems' => $this->getMenuItems(),
        ]);
    }
}
