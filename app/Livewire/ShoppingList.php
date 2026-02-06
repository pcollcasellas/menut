<?php

namespace App\Livewire;

use App\Livewire\Concerns\BelongsToHousehold;
use App\Models\MenuItem;
use App\Models\ShoppingListItem;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShoppingList extends Component
{
    use BelongsToHousehold;

    public string $newItemName = '';

    public bool $showAddFromRecipes = false;

    public string $startDate = '';

    public string $startMealType = 'lunch';

    public string $endDate = '';

    public string $endMealType = 'dinner';

    public function mount(): void
    {
        // Default date range: current week (Monday to Sunday)
        $this->startDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
    }

    #[Computed]
    public function toBuyItems()
    {
        return ShoppingListItem::where('household_id', $this->householdId())
            ->where('is_bought', false)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function boughtItems()
    {
        return ShoppingListItem::where('household_id', $this->householdId())
            ->where('is_bought', true)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function addItem(): void
    {
        $name = trim($this->newItemName);

        if ($name === '') {
            return;
        }

        // Check if item already exists (case-insensitive) and is not bought
        $existing = ShoppingListItem::where('household_id', $this->householdId())
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where('is_bought', false)
            ->first();

        if ($existing) {
            // Increment quantity if exists
            $existing->increment('quantity');
        } else {
            ShoppingListItem::create([
                'household_id' => $this->householdId(),
                'user_id' => auth()->id(),
                'name' => $name,
                'quantity' => 1,
                'is_bought' => false,
            ]);
        }

        $this->newItemName = '';
    }

    public function incrementQuantity(int $itemId): void
    {
        $item = ShoppingListItem::where('household_id', $this->householdId())
            ->findOrFail($itemId);

        $item->increment('quantity');
    }

    public function decrementQuantity(int $itemId): void
    {
        $item = ShoppingListItem::where('household_id', $this->householdId())
            ->findOrFail($itemId);

        if ($item->quantity <= 1) {
            $item->delete();
        } else {
            $item->decrement('quantity');
        }
    }

    public function toggleBought(int $itemId): void
    {
        $item = ShoppingListItem::where('household_id', $this->householdId())
            ->findOrFail($itemId);

        $item->update(['is_bought' => ! $item->is_bought]);
    }

    public function deleteItem(int $itemId): void
    {
        ShoppingListItem::where('household_id', $this->householdId())
            ->findOrFail($itemId)
            ->delete();
    }

    public function clearAllBought(): void
    {
        ShoppingListItem::where('household_id', $this->householdId())
            ->where('is_bought', true)
            ->delete();
    }

    public function clearAll(): void
    {
        ShoppingListItem::where('household_id', $this->householdId())
            ->delete();
    }

    public function toggleAddFromRecipes(): void
    {
        $this->showAddFromRecipes = ! $this->showAddFromRecipes;
    }

    public function addFromRecipes(): void
    {
        // Parse dates from Y-m-d format (native date input format)
        $startDateObj = Carbon::parse($this->startDate)->startOfDay();
        $endDateObj = Carbon::parse($this->endDate)->endOfDay();

        // Get all menu items in the date range
        $menuItems = MenuItem::with('recipe.ingredientItems')
            ->where('household_id', $this->householdId())
            ->whereDate('date', '>=', $startDateObj)
            ->whereDate('date', '<=', $endDateObj)
            ->get();

        // Filter by meal type bounds
        $menuItems = $menuItems->filter(function ($item) use ($startDateObj, $endDateObj) {
            // If same date as start, check meal type
            if ($item->date->isSameDay($startDateObj)) {
                if ($this->startMealType === 'dinner' && $item->meal_type === 'lunch') {
                    return false;
                }
            }

            // If same date as end, check meal type
            if ($item->date->isSameDay($endDateObj)) {
                if ($this->endMealType === 'lunch' && $item->meal_type === 'dinner') {
                    return false;
                }
            }

            return true;
        });

        // Count ingredients across all recipes (case-insensitive)
        $ingredientCounts = [];
        foreach ($menuItems as $menuItem) {
            if ($menuItem->recipe && $menuItem->recipe->ingredientItems) {
                foreach ($menuItem->recipe->ingredientItems as $ingredient) {
                    $key = strtolower($ingredient->name);
                    if (! isset($ingredientCounts[$key])) {
                        $ingredientCounts[$key] = [
                            'name' => $ingredient->name,
                            'count' => 0,
                        ];
                    }
                    $ingredientCounts[$key]['count']++;
                }
            }
        }

        // Add or update each ingredient with quantity
        foreach ($ingredientCounts as $data) {
            $existing = ShoppingListItem::where('household_id', $this->householdId())
                ->whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
                ->where('is_bought', false)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $data['count']);
            } else {
                ShoppingListItem::create([
                    'household_id' => $this->householdId(),
                    'user_id' => auth()->id(),
                    'name' => $data['name'],
                    'quantity' => $data['count'],
                    'is_bought' => false,
                ]);
            }
        }

        $this->showAddFromRecipes = false;
    }

    public function render()
    {
        return view('livewire.shopping-list');
    }
}
