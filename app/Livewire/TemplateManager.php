<?php

namespace App\Livewire;

use App\Enums\MealType;
use App\Livewire\Concerns\BelongsToHousehold;
use App\Models\MenuItem;
use App\Models\MenuTemplate;
use App\Models\MenuTemplateItem;
use App\Models\Recipe;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TemplateManager extends Component
{
    use BelongsToHousehold;

    public bool $showModal = false;

    public string $mode = 'list'; // list, save, import, edit

    public string $weekStart = '';

    public string $templateName = '';

    public ?int $selectedTemplateId = null;

    public string $importMode = 'skip'; // skip, replace

    // Edit mode properties
    public ?int $editingTemplateId = null;

    public string $editingTemplateName = '';

    public array $editingSlots = []; // ['0_lunch' => 5, '0_dinner' => null, ...]

    public ?string $activeSlot = null; // Currently open dropdown: '0_lunch', '1_dinner', etc.

    protected function rules(): array
    {
        $rules = [
            'templateName' => 'required|string|max:255',
        ];

        if ($this->mode === 'edit') {
            $rules['editingTemplateName'] = 'required|string|max:255';
        }

        return $rules;
    }

    #[Computed]
    public function mealTypes(): array
    {
        $types = [];

        if (auth()->user()->show_breakfast ?? false) {
            $types[] = MealType::Breakfast;
        }

        $types[] = MealType::Lunch;
        $types[] = MealType::Dinner;

        return $types;
    }

    #[On('open-template-manager')]
    public function open(string $weekStart): void
    {
        $this->weekStart = $weekStart;
        $this->mode = 'list';
        $this->templateName = '';
        $this->selectedTemplateId = null;
        $this->importMode = 'skip';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
    }

    public function showSaveForm(): void
    {
        $this->mode = 'save';
        $this->templateName = '';
    }

    public function showImportForm(int $templateId): void
    {
        $this->selectedTemplateId = $templateId;
        $this->mode = 'import';
        $this->importMode = 'skip';
    }

    public function backToList(): void
    {
        $this->mode = 'list';
        $this->selectedTemplateId = null;
        $this->editingTemplateId = null;
        $this->editingTemplateName = '';
        $this->editingSlots = [];
        $this->activeSlot = null;
        $this->resetValidation();
    }

    public function showEditForm(int $templateId): void
    {
        $template = MenuTemplate::where('household_id', $this->householdId())
            ->with('items')
            ->findOrFail($templateId);

        $this->editingTemplateId = $template->id;
        $this->editingTemplateName = $template->name;

        // Initialize all slots as empty
        $this->editingSlots = [];
        for ($day = 0; $day < 7; $day++) {
            foreach ($this->mealTypes as $mealType) {
                $this->editingSlots["{$day}_{$mealType->value}"] = null;
            }
        }

        // Fill in existing items
        foreach ($template->items as $item) {
            $key = "{$item->day_of_week}_{$item->meal_type->value}";
            $this->editingSlots[$key] = $item->recipe_id;
        }

        $this->activeSlot = null;
        $this->mode = 'edit';
    }

    public function toggleSlotSelector(string $slot): void
    {
        if ($this->activeSlot === $slot) {
            $this->activeSlot = null;
        } else {
            $this->activeSlot = $slot;
        }
    }

    public function updateSlot(string $slot, ?int $recipeId): void
    {
        $this->editingSlots[$slot] = $recipeId;
        $this->activeSlot = null;
    }

    public function saveTemplateChanges(): void
    {
        $this->validate(['editingTemplateName' => 'required|string|max:255']);

        $template = MenuTemplate::where('household_id', $this->householdId())
            ->findOrFail($this->editingTemplateId);

        // Update template name
        $template->update(['name' => $this->editingTemplateName]);

        // Delete existing items
        $template->items()->delete();

        // Create new items from editingSlots
        foreach ($this->editingSlots as $key => $recipeId) {
            if ($recipeId) {
                [$day, $mealType] = explode('_', $key);
                MenuTemplateItem::create([
                    'menu_template_id' => $template->id,
                    'recipe_id' => $recipeId,
                    'day_of_week' => (int) $day,
                    'meal_type' => $mealType,
                ]);
            }
        }

        $this->backToList();
    }

    public function getEditingRecipes()
    {
        return Recipe::where('household_id', $this->householdId())
            ->orderBy('name')
            ->get();
    }

    public function saveTemplate(): void
    {
        $this->validate();

        $template = MenuTemplate::create([
            'household_id' => $this->householdId(),
            'user_id' => auth()->id(),
            'name' => $this->templateName,
        ]);

        $start = Carbon::parse($this->weekStart);

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);

            foreach ($this->mealTypes as $mealType) {
                $menuItem = MenuItem::where('household_id', $this->householdId())
                    ->whereDate('date', $date)
                    ->where('meal_type', $mealType)
                    ->first();

                if ($menuItem && $menuItem->recipe_id) {
                    MenuTemplateItem::create([
                        'menu_template_id' => $template->id,
                        'recipe_id' => $menuItem->recipe_id,
                        'day_of_week' => $i,
                        'meal_type' => $mealType,
                    ]);
                }
            }
        }

        $this->closeModal();
        $this->dispatch('menu-updated');
    }

    public function importTemplate(): void
    {
        $template = MenuTemplate::where('household_id', $this->householdId())
            ->with('items')
            ->findOrFail($this->selectedTemplateId);

        $start = Carbon::parse($this->weekStart);

        if ($this->importMode === 'replace') {
            // Delete all menu items for this week
            MenuItem::where('household_id', $this->householdId())
                ->whereBetween('date', [$start, $start->copy()->addDays(6)])
                ->delete();
        }

        foreach ($template->items as $item) {
            // Skip items where recipe was deleted
            if (! $item->recipe_id) {
                continue;
            }

            $date = $start->copy()->addDays($item->day_of_week);

            $existing = MenuItem::where('household_id', $this->householdId())
                ->whereDate('date', $date)
                ->where('meal_type', $item->meal_type)
                ->first();

            if ($this->importMode === 'skip' && $existing) {
                continue;
            }

            if ($existing) {
                $existing->update(['recipe_id' => $item->recipe_id]);
            } else {
                MenuItem::create([
                    'household_id' => $this->householdId(),
                    'user_id' => auth()->id(),
                    'date' => $date,
                    'meal_type' => $item->meal_type->value,
                    'recipe_id' => $item->recipe_id,
                ]);
            }
        }

        $this->closeModal();
        $this->dispatch('menu-updated');
    }

    public function deleteTemplate(int $id): void
    {
        MenuTemplate::where('household_id', $this->householdId())->findOrFail($id)->delete();
    }

    public function getTemplates()
    {
        return MenuTemplate::where('household_id', $this->householdId())
            ->withCount('items')
            ->orderBy('name')
            ->get();
    }

    public function getSelectedTemplate()
    {
        if (! $this->selectedTemplateId) {
            return null;
        }

        return MenuTemplate::where('household_id', $this->householdId())
            ->with('items.recipe')
            ->find($this->selectedTemplateId);
    }

    public function getSelectedTemplatePreview(): array
    {
        $preview = [];
        for ($day = 0; $day < 7; $day++) {
            $preview[$day] = [];
            foreach ($this->mealTypes as $mealType) {
                $preview[$day][$mealType->value] = null;
            }
        }

        $template = $this->getSelectedTemplate();
        if (! $template) {
            return $preview;
        }

        foreach ($template->items as $item) {
            if (! $item->recipe) {
                continue;
            }

            $preview[$item->day_of_week][$item->meal_type->value] = $item->recipe->name;
        }

        return $preview;
    }

    public function render()
    {
        return view('livewire.template-manager', [
            'templates' => $this->getTemplates(),
            'selectedTemplate' => $this->getSelectedTemplate(),
            'selectedTemplatePreview' => $this->getSelectedTemplatePreview(),
            'recipes' => $this->mode === 'edit' ? $this->getEditingRecipes() : collect(),
        ]);
    }
}
