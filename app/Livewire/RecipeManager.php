<?php

namespace App\Livewire;

use App\Livewire\Concerns\BelongsToHousehold;
use App\Models\Ingredient;
use App\Models\Recipe;
use Livewire\Attributes\Computed;
use Livewire\Component;

class RecipeManager extends Component
{
    use BelongsToHousehold;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public array $selectedIngredients = [];

    public string $ingredientSearch = '';

    public string $instructions = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selectedIngredients' => 'nullable|array',
            'selectedIngredients.*' => 'string|max:255',
            'instructions' => 'nullable|string',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
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
            ]);
        } else {
            $recipe = Recipe::create([
                'household_id' => $this->householdId(),
                'user_id' => auth()->id(),
                'name' => $this->name,
                'description' => $this->description,
                'instructions' => $this->instructions,
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
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.recipe-manager', [
            'recipes' => Recipe::where('household_id', $this->householdId())
                ->with('ingredientItems')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
