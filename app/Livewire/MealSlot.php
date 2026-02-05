<?php

namespace App\Livewire;

use App\Livewire\Concerns\BelongsToHousehold;
use App\Models\MenuItem;
use App\Models\Recipe;
use Livewire\Attributes\On;
use Livewire\Component;

class MealSlot extends Component
{
    use BelongsToHousehold;

    public string $date;

    public string $mealType;

    public bool $showSelector = false;

    public ?int $selectedRecipeId = null;

    public string $searchQuery = '';

    protected $listeners = ['selectorOpened' => 'handleSelectorOpened'];

    public function mount(string $date, string $mealType): void
    {
        $this->date = $date;
        $this->mealType = $mealType;

        $this->refreshFromMenu();
    }

    #[On('menu-updated')]
    public function refreshFromMenu(): void
    {
        $menuItem = MenuItem::where('household_id', $this->householdId())
            ->whereDate('date', $this->date)
            ->where('meal_type', $this->mealType)
            ->first();

        $this->selectedRecipeId = $menuItem?->recipe_id;
        $this->showSelector = false;
        $this->searchQuery = '';
    }

    public function toggleSelector(): void
    {
        $wasOpen = $this->showSelector;
        $this->showSelector = ! $this->showSelector;

        // Clear search query when closing
        if (! $this->showSelector) {
            $this->searchQuery = '';
        }

        // If we're opening this selector, notify all other components to close
        if ($this->showSelector && ! $wasOpen) {
            $this->dispatch('selectorOpened', componentId: $this->getId());
        }
    }

    public function handleSelectorOpened($componentId): void
    {
        // Close this selector if it's not the one that just opened
        if ($componentId !== $this->getId() && $this->showSelector) {
            $this->showSelector = false;
            $this->searchQuery = '';
        }
    }

    public function selectRecipe(?int $recipeId): void
    {
        if ($recipeId === null) {
            MenuItem::where('household_id', $this->householdId())
                ->whereDate('date', $this->date)
                ->where('meal_type', $this->mealType)
                ->delete();
            $this->selectedRecipeId = null;
        } else {
            $existing = MenuItem::where('household_id', $this->householdId())
                ->whereDate('date', $this->date)
                ->where('meal_type', $this->mealType)
                ->first();

            if ($existing) {
                $existing->update(['recipe_id' => $recipeId]);
            } else {
                MenuItem::create([
                    'household_id' => $this->householdId(),
                    'user_id' => auth()->id(),
                    'date' => $this->date,
                    'meal_type' => $this->mealType,
                    'recipe_id' => $recipeId,
                ]);
            }
            $this->selectedRecipeId = $recipeId;
        }

        $this->showSelector = false;
        $this->searchQuery = '';
        $this->dispatch('menu-updated');
    }

    public function getCurrentRecipe(): ?Recipe
    {
        if (! $this->selectedRecipeId) {
            return null;
        }

        return Recipe::find($this->selectedRecipeId);
    }

    public function render()
    {
        $recipesQuery = Recipe::where('household_id', $this->householdId());

        if (trim($this->searchQuery) !== '') {
            $recipesQuery->where('name', 'like', '%'.$this->searchQuery.'%');
        }

        return view('livewire.meal-slot', [
            'currentRecipe' => $this->getCurrentRecipe(),
            'recipes' => $recipesQuery->orderBy('name')->get(),
        ]);
    }
}
