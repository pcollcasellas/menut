<?php

namespace App\Livewire;

use App\Models\MenuItem;
use App\Models\Recipe;
use Livewire\Component;

class MealSlot extends Component
{
    public string $date;

    public string $mealType;

    public bool $showSelector = false;

    public ?int $selectedRecipeId = null;

    protected $listeners = ['selectorOpened' => 'handleSelectorOpened'];

    public function mount(string $date, string $mealType): void
    {
        $this->date = $date;
        $this->mealType = $mealType;

        $menuItem = MenuItem::where('user_id', auth()->id())
            ->whereDate('date', $date)
            ->where('meal_type', $mealType)
            ->first();

        $this->selectedRecipeId = $menuItem?->recipe_id;
    }

    public function toggleSelector(): void
    {
        $wasOpen = $this->showSelector;
        $this->showSelector = ! $this->showSelector;

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
        }
    }

    public function selectRecipe(?int $recipeId): void
    {
        if ($recipeId === null) {
            MenuItem::where('user_id', auth()->id())
                ->whereDate('date', $this->date)
                ->where('meal_type', $this->mealType)
                ->delete();
            $this->selectedRecipeId = null;
        } else {
            $existing = MenuItem::where('user_id', auth()->id())
                ->whereDate('date', $this->date)
                ->where('meal_type', $this->mealType)
                ->first();

            if ($existing) {
                $existing->update(['recipe_id' => $recipeId]);
            } else {
                MenuItem::create([
                    'user_id' => auth()->id(),
                    'date' => $this->date,
                    'meal_type' => $this->mealType,
                    'recipe_id' => $recipeId,
                ]);
            }
            $this->selectedRecipeId = $recipeId;
        }

        $this->showSelector = false;
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
        return view('livewire.meal-slot', [
            'currentRecipe' => $this->getCurrentRecipe(),
            'recipes' => Recipe::where('user_id', auth()->id())->orderBy('name')->get(),
        ]);
    }
}
