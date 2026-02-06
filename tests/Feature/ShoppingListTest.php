<?php

namespace Tests\Feature;

use App\Livewire\ShoppingList;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShoppingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_shopping_list_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/shopping-list');

        $response->assertOk();
    }

    public function test_can_add_item_manually(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->set('newItemName', 'Milk')
            ->call('addItem');

        $this->assertDatabaseHas('shopping_list_items', [
            'household_id' => $user->household_id,
            'name' => 'Milk',
            'is_bought' => false,
        ]);
    }

    public function test_cannot_add_empty_item(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->set('newItemName', '   ')
            ->call('addItem');

        $this->assertEquals(0, ShoppingListItem::count());
    }

    public function test_adding_duplicate_item_increments_quantity(): void
    {
        $user = User::factory()->create();
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'quantity' => 1,
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->set('newItemName', 'MILK')
            ->call('addItem');

        // Still one item, but quantity incremented
        $this->assertEquals(1, ShoppingListItem::where('household_id', $user->household_id)->count());
        $this->assertDatabaseHas('shopping_list_items', ['name' => 'Milk', 'quantity' => 2]);
    }

    public function test_can_add_duplicate_if_previous_is_bought(): void
    {
        $user = User::factory()->create();
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'is_bought' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->set('newItemName', 'Milk')
            ->call('addItem');

        $this->assertEquals(2, ShoppingListItem::where('household_id', $user->household_id)->count());
    }

    public function test_can_toggle_bought_status(): void
    {
        $user = User::factory()->create();
        $item = ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->call('toggleBought', $item->id);

        $item->refresh();
        $this->assertTrue($item->is_bought);
    }

    public function test_can_uncheck_bought_item(): void
    {
        $user = User::factory()->create();
        $item = ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'is_bought' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->call('toggleBought', $item->id);

        $item->refresh();
        $this->assertFalse($item->is_bought);
    }

    public function test_can_delete_item(): void
    {
        $user = User::factory()->create();
        $item = ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->call('deleteItem', $item->id);

        $this->assertDatabaseMissing('shopping_list_items', ['id' => $item->id]);
    }

    public function test_can_increment_quantity(): void
    {
        $user = User::factory()->create();
        $item = ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'quantity' => 1,
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->call('incrementQuantity', $item->id);

        $this->assertDatabaseHas('shopping_list_items', ['id' => $item->id, 'quantity' => 2]);
    }

    public function test_can_decrement_quantity(): void
    {
        $user = User::factory()->create();
        $item = ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'quantity' => 3,
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->call('decrementQuantity', $item->id);

        $this->assertDatabaseHas('shopping_list_items', ['id' => $item->id, 'quantity' => 2]);
    }

    public function test_decrementing_quantity_to_zero_deletes_item(): void
    {
        $user = User::factory()->create();
        $item = ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'quantity' => 1,
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->call('decrementQuantity', $item->id);

        $this->assertDatabaseMissing('shopping_list_items', ['id' => $item->id]);
    }

    public function test_can_clear_all_bought(): void
    {
        $user = User::factory()->create();
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'is_bought' => true,
        ]);
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Bread',
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->call('clearAllBought');

        $this->assertEquals(1, ShoppingListItem::where('household_id', $user->household_id)->count());
        $this->assertDatabaseHas('shopping_list_items', ['name' => 'Bread']);
        $this->assertDatabaseMissing('shopping_list_items', ['name' => 'Milk']);
    }

    public function test_can_clear_all(): void
    {
        $user = User::factory()->create();
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Milk',
            'is_bought' => true,
        ]);
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Bread',
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->call('clearAll');

        $this->assertEquals(0, ShoppingListItem::where('household_id', $user->household_id)->count());
    }

    public function test_shopping_list_is_household_scoped(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        ShoppingListItem::create([
            'household_id' => $userA->household_id,
            'user_id' => $userA->id,
            'name' => 'User A Item',
            'is_bought' => false,
        ]);
        ShoppingListItem::create([
            'household_id' => $userB->household_id,
            'user_id' => $userB->id,
            'name' => 'User B Item',
            'is_bought' => false,
        ]);

        $this->actingAs($userA);

        $component = Livewire::test(ShoppingList::class);

        $toBuyItems = $component->instance()->toBuyItems;
        $this->assertEquals(1, $toBuyItems->count());
        $this->assertEquals('User A Item', $toBuyItems->first()->name);
    }

    public function test_same_household_users_see_same_list(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);

        ShoppingListItem::create([
            'household_id' => $userA->household_id,
            'user_id' => $userA->id,
            'name' => 'Shared Item',
            'is_bought' => false,
        ]);

        $this->actingAs($userB);

        $component = Livewire::test(ShoppingList::class);

        $toBuyItems = $component->instance()->toBuyItems;
        $this->assertEquals(1, $toBuyItems->count());
        $this->assertEquals('Shared Item', $toBuyItems->first()->name);
    }

    public function test_can_add_ingredients_from_recipes(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);

        $ingredient1 = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Tomatoes']);
        $ingredient2 = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Onions']);
        $recipe->ingredientItems()->attach([$ingredient1->id, $ingredient2->id]);

        $date = Carbon::now();
        MenuItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'meal_type' => 'lunch',
            'recipe_id' => $recipe->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->set('startDate', $date->format('Y-m-d'))
            ->set('startMealType', 'lunch')
            ->set('endDate', $date->format('Y-m-d'))
            ->set('endMealType', 'dinner')
            ->call('addFromRecipes');

        $this->assertDatabaseHas('shopping_list_items', [
            'household_id' => $user->household_id,
            'name' => 'Tomatoes',
        ]);
        $this->assertDatabaseHas('shopping_list_items', [
            'household_id' => $user->household_id,
            'name' => 'Onions',
        ]);
    }

    public function test_add_from_recipes_aggregates_same_ingredient_with_count(): void
    {
        $user = User::factory()->create();

        // Create two recipes with the same ingredient
        $recipe1 = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);
        $recipe2 = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);

        $ingredient = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Tomatoes']);
        $recipe1->ingredientItems()->attach($ingredient->id);
        $recipe2->ingredientItems()->attach($ingredient->id);

        $date = Carbon::now();
        MenuItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'meal_type' => 'lunch',
            'recipe_id' => $recipe1->id,
        ]);
        MenuItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'meal_type' => 'dinner',
            'recipe_id' => $recipe2->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->set('startDate', $date->format('Y-m-d'))
            ->set('startMealType', 'lunch')
            ->set('endDate', $date->format('Y-m-d'))
            ->set('endMealType', 'dinner')
            ->call('addFromRecipes');

        // ONE shopping list item with quantity 2
        $this->assertEquals(1, ShoppingListItem::where('household_id', $user->household_id)->count());
        $this->assertDatabaseHas('shopping_list_items', ['name' => 'Tomatoes', 'quantity' => 2]);
    }

    public function test_add_from_recipes_increments_existing_item_quantity(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);

        $ingredient = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Tomatoes']);
        $recipe->ingredientItems()->attach($ingredient->id);

        // Already have this item in shopping list with quantity 1
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Tomatoes',
            'quantity' => 1,
            'is_bought' => false,
        ]);

        $date = Carbon::now();
        MenuItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'meal_type' => 'lunch',
            'recipe_id' => $recipe->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->set('startDate', $date->format('Y-m-d'))
            ->set('startMealType', 'lunch')
            ->set('endDate', $date->format('Y-m-d'))
            ->set('endMealType', 'dinner')
            ->call('addFromRecipes');

        // Still ONE item but quantity incremented to 2
        $this->assertEquals(1, ShoppingListItem::where('household_id', $user->household_id)->count());
        $this->assertDatabaseHas('shopping_list_items', ['name' => 'Tomatoes', 'quantity' => 2]);
    }

    public function test_add_from_recipes_respects_meal_type_bounds(): void
    {
        $user = User::factory()->create();

        $recipe1 = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);
        $recipe2 = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);

        $ingredient1 = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Lunch Item']);
        $ingredient2 = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Dinner Item']);
        $recipe1->ingredientItems()->attach($ingredient1->id);
        $recipe2->ingredientItems()->attach($ingredient2->id);

        $date = Carbon::now();
        MenuItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'meal_type' => 'lunch',
            'recipe_id' => $recipe1->id,
        ]);
        MenuItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'date' => $date->format('Y-m-d'),
            'meal_type' => 'dinner',
            'recipe_id' => $recipe2->id,
        ]);

        $this->actingAs($user);

        // Only select dinner on start date
        Livewire::test(ShoppingList::class)
            ->set('startDate', $date->format('Y-m-d'))
            ->set('startMealType', 'dinner')
            ->set('endDate', $date->format('Y-m-d'))
            ->set('endMealType', 'dinner')
            ->call('addFromRecipes');

        // Only dinner item should be added
        $this->assertDatabaseHas('shopping_list_items', ['name' => 'Dinner Item']);
        $this->assertDatabaseMissing('shopping_list_items', ['name' => 'Lunch Item']);
    }

    public function test_to_buy_items_sorted_by_name(): void
    {
        $user = User::factory()->create();
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Zebra Meat',
            'is_bought' => false,
        ]);
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Apples',
            'is_bought' => false,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ShoppingList::class);

        $toBuyItems = $component->instance()->toBuyItems;
        $this->assertEquals('Apples', $toBuyItems->first()->name);
        $this->assertEquals('Zebra Meat', $toBuyItems->last()->name);
    }

    public function test_bought_items_sorted_by_updated_at_desc(): void
    {
        $user = User::factory()->create();

        // Create first item with older timestamp
        $item1 = ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'First Bought',
            'is_bought' => true,
        ]);
        // Use DB update to avoid model timestamp refresh
        \Illuminate\Support\Facades\DB::table('shopping_list_items')
            ->where('id', $item1->id)
            ->update(['updated_at' => now()->subHour()]);

        // Create second item (will have more recent timestamp)
        ShoppingListItem::create([
            'household_id' => $user->household_id,
            'user_id' => $user->id,
            'name' => 'Last Bought',
            'is_bought' => true,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(ShoppingList::class);

        $boughtItems = $component->instance()->boughtItems;
        $this->assertEquals('Last Bought', $boughtItems->first()->name);
        $this->assertEquals('First Bought', $boughtItems->last()->name);
    }

    public function test_toggle_add_from_recipes_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->assertSet('showAddFromRecipes', false)
            ->call('toggleAddFromRecipes')
            ->assertSet('showAddFromRecipes', true)
            ->call('toggleAddFromRecipes')
            ->assertSet('showAddFromRecipes', false);
    }

    public function test_add_from_recipes_closes_panel(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now();

        $this->actingAs($user);

        Livewire::test(ShoppingList::class)
            ->set('showAddFromRecipes', true)
            ->set('startDate', $date->format('Y-m-d'))
            ->set('endDate', $date->format('Y-m-d'))
            ->call('addFromRecipes')
            ->assertSet('showAddFromRecipes', false);
    }

    public function test_cannot_toggle_other_household_item(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $item = ShoppingListItem::create([
            'household_id' => $userA->household_id,
            'user_id' => $userA->id,
            'name' => 'User A Item',
            'is_bought' => false,
        ]);

        $this->actingAs($userB);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(ShoppingList::class)
            ->call('toggleBought', $item->id);
    }

    public function test_cannot_delete_other_household_item(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $item = ShoppingListItem::create([
            'household_id' => $userA->household_id,
            'user_id' => $userA->id,
            'name' => 'User A Item',
            'is_bought' => false,
        ]);

        $this->actingAs($userB);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(ShoppingList::class)
            ->call('deleteItem', $item->id);
    }
}
