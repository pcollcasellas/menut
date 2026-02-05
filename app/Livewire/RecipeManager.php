<?php

namespace App\Livewire;

use App\Livewire\Concerns\BelongsToHousehold;
use App\Models\Recipe;
use Livewire\Component;

class RecipeManager extends Component
{
    use BelongsToHousehold;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public string $ingredients = '';

    public string $instructions = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ingredients' => 'nullable|string',
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
        $recipe = Recipe::where('household_id', $this->householdId())->findOrFail($id);
        $this->editingId = $recipe->id;
        $this->name = $recipe->name;
        $this->description = $recipe->description ?? '';
        $this->ingredients = $recipe->ingredients ?? '';
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
                'ingredients' => $this->ingredients,
                'instructions' => $this->instructions,
            ]);
        } else {
            Recipe::create([
                'household_id' => $this->householdId(),
                'user_id' => auth()->id(),
                'name' => $this->name,
                'description' => $this->description,
                'ingredients' => $this->ingredients,
                'instructions' => $this->instructions,
            ]);
        }

        $this->resetForm();
        $this->showForm = false;
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
        $this->ingredients = '';
        $this->instructions = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.recipe-manager', [
            'recipes' => Recipe::where('household_id', $this->householdId())->orderBy('name')->get(),
        ]);
    }
}
