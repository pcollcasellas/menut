<?php

namespace App\Livewire;

use App\Enums\RecipeType;
use App\Livewire\Concerns\BelongsToHousehold;
use App\Models\Ingredient;
use App\Models\Recipe;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class RecipeManager extends Component
{
    use BelongsToHousehold;

    #[Url]
    public string $recipeType = 'meal';

    public bool $showForm = false;

    public function mount(): void
    {
        // If user has breakfast disabled but is trying to view breakfast recipes, redirect to meal
        if ($this->recipeType === 'breakfast' && ! (auth()->user()->show_breakfast ?? false)) {
            $this->recipeType = 'meal';
        }
    }

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public array $selectedIngredients = [];

    public string $ingredientSearch = '';

    public string $instructions = '';

    public string $type = 'meal';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selectedIngredients' => 'nullable|array',
            'selectedIngredients.*' => 'string|max:255',
            'instructions' => 'nullable|string',
            'type' => 'required|in:breakfast,meal',
        ];
    }

    public function setRecipeType(string $type): void
    {
        $this->recipeType = $type;
        $this->showForm = false;
        $this->resetForm();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->type = $this->recipeType;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $recipe = Recipe::where('household_id', $this->householdId())
            ->with('ingredientItems')
            ->findOrFail($id);

        $this->editingId = $recipe->id;
        $this->name = $recipe->name;
        $this->description = $recipe->description ?? '';
        $this->selectedIngredients = $recipe->ingredientItems->pluck('name')->toArray();
        $this->instructions = $recipe->instructions ?? '';
        $this->type = $recipe->type->value;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $recipe = Recipe::where('household_id', $this->householdId())->findOrFail($this->editingId);
            $recipe->update([
                'name' => $this->name,
                'description' => $this->description,
                'instructions' => $this->instructions,
                'type' => $this->type,
            ]);
        } else {
            $recipe = Recipe::create([
                'household_id' => $this->householdId(),
                'user_id' => auth()->id(),
                'name' => $this->name,
                'description' => $this->description,
                'instructions' => $this->instructions,
                'type' => $this->type,
            ]);
        }

        // Sync ingredients
        $this->syncIngredients($recipe);

        $this->resetForm();
        $this->showForm = false;
    }

    private function syncIngredients(Recipe $recipe): void
    {
        $ingredientIds = [];

        foreach ($this->selectedIngredients as $ingredientName) {
            $ingredient = Ingredient::findOrCreateForHousehold(
                $this->householdId(),
                $ingredientName
            );
            $ingredientIds[] = $ingredient->id;
        }

        $recipe->ingredientItems()->sync($ingredientIds);
    }

    public function addIngredient(): void
    {
        $name = trim($this->ingredientSearch);

        if ($name === '') {
            return;
        }

        // Check if already added (case-insensitive)
        $alreadyExists = collect($this->selectedIngredients)
            ->contains(fn ($existing) => strtolower($existing) === strtolower($name));

        if (! $alreadyExists) {
            $this->selectedIngredients[] = $name;
        }

        $this->ingredientSearch = '';
    }

    public function removeIngredient(int $index): void
    {
        unset($this->selectedIngredients[$index]);
        $this->selectedIngredients = array_values($this->selectedIngredients);
    }

    public function selectSuggestion(string $name): void
    {
        // Check if already added (case-insensitive)
        $alreadyExists = collect($this->selectedIngredients)
            ->contains(fn ($existing) => strtolower($existing) === strtolower($name));

        if (! $alreadyExists) {
            $this->selectedIngredients[] = $name;
        }

        $this->ingredientSearch = '';
    }

    #[Computed]
    public function ingredientSuggestions(): array
    {
        if (strlen($this->ingredientSearch) < 1) {
            return [];
        }

        return Ingredient::where('household_id', $this->householdId())
            ->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($this->ingredientSearch).'%'])
            ->whereNotIn('name', $this->selectedIngredients)
            ->orderBy('name')
            ->limit(10)
            ->pluck('name')
            ->toArray();
    }

    public function delete(int $id): void
    {
        Recipe::where('household_id', $this->householdId())->findOrFail($id)->delete();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->selectedIngredients = [];
        $this->ingredientSearch = '';
        $this->instructions = '';
        $this->type = $this->recipeType;
        $this->resetValidation();
    }

    #[Computed]
    public function currentRecipeType(): RecipeType
    {
        return RecipeType::from($this->recipeType);
    }

    #[Computed]
    public function availableRecipeTypes(): array
    {
        $types = [RecipeType::Meal];

        if (auth()->user()->show_breakfast ?? false) {
            array_unshift($types, RecipeType::Breakfast);
        }

        return $types;
    }

    public function render()
    {
        return view('livewire.recipe-manager', [
            'recipes' => Recipe::where('household_id', $this->householdId())
                ->where('type', $this->recipeType)
                ->with('ingredientItems')
                ->orderBy('name')
                ->get(),
            'recipeTypes' => $this->availableRecipeTypes,
        ]);
    }
}
