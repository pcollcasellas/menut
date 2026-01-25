<?php

namespace Tests\Feature;

use App\Livewire\MealSlot;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MealSlotTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_select_recipe_for_meal_slot(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pasta Carbonara']);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->call('selectRecipe', $recipe->id);

        $menuItem = MenuItem::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->where('meal_type', 'lunch')
            ->first();

        $this->assertNotNull($menuItem);
        $this->assertEquals($recipe->id, $menuItem->recipe_id);
    }

    public function test_can_remove_recipe_from_meal_slot(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $date = Carbon::now()->format('Y-m-d');

        MenuItem::create([
            'user_id' => $user->id,
            'date' => $date,
            'meal_type' => 'lunch',
            'recipe_id' => $recipe->id,
        ]);

        $this->actingAs($user);

        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->call('selectRecipe', null);

        $this->assertDatabaseMissing('menu_items', [
            'user_id' => $user->id,
            'date' => $date,
            'meal_type' => 'lunch',
        ]);
    }

    public function test_can_replace_recipe_in_meal_slot(): void
    {
        $user = User::factory()->create();
        $recipeA = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Recipe A']);
        $recipeB = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Recipe B']);
        $date = Carbon::now()->format('Y-m-d');

        MenuItem::create([
            'user_id' => $user->id,
            'date' => $date,
            'meal_type' => 'lunch',
            'recipe_id' => $recipeA->id,
        ]);

        $this->actingAs($user);

        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->call('selectRecipe', $recipeB->id);

        $menuItem = MenuItem::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->where('meal_type', 'lunch')
            ->first();

        $this->assertNotNull($menuItem);
        $this->assertEquals($recipeB->id, $menuItem->recipe_id);

        $this->assertEquals(1, MenuItem::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->where('meal_type', 'lunch')
            ->count());
    }

    public function test_toggle_selector_opens_and_closes_dropdown(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->assertSet('showSelector', false)
            ->call('toggleSelector')
            ->assertSet('showSelector', true)
            ->call('toggleSelector')
            ->assertSet('showSelector', false);
    }

    public function test_search_filters_recipes_by_name(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pasta Carbonara']);
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pizza Margherita']);
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Chicken Curry']);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        $component = Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->set('searchQuery', 'pasta');

        $recipes = $component->viewData('recipes');
        $this->assertEquals(1, $recipes->count());
        $this->assertEquals('Pasta Carbonara', $recipes->first()->name);
    }

    public function test_search_is_case_insensitive(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pasta Carbonara']);
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pizza Margherita']);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        $component = Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->set('searchQuery', 'PASTA');

        $recipes = $component->viewData('recipes');
        $this->assertEquals(1, $recipes->count());
        $this->assertEquals('Pasta Carbonara', $recipes->first()->name);
    }

    public function test_search_returns_partial_matches(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pasta Carbonara']);
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pasta Bolognese']);
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pizza Margherita']);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        $component = Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->set('searchQuery', 'arbo');

        $recipes = $component->viewData('recipes');
        $this->assertEquals(1, $recipes->count());
        $this->assertEquals('Pasta Carbonara', $recipes->first()->name);
    }

    public function test_search_returns_empty_when_no_matches(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pasta Carbonara']);
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pizza Margherita']);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        $component = Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->set('searchQuery', 'sushi');

        $recipes = $component->viewData('recipes');
        $this->assertEquals(0, $recipes->count());
    }

    public function test_search_query_clears_when_closing_selector(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->set('searchQuery', 'pasta')
            ->assertSet('searchQuery', 'pasta')
            ->call('toggleSelector')
            ->call('toggleSelector')
            ->assertSet('searchQuery', '');
    }

    public function test_search_query_clears_when_selecting_recipe(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Pasta Carbonara']);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->set('searchQuery', 'pasta')
            ->assertSet('searchQuery', 'pasta')
            ->call('selectRecipe', $recipe->id)
            ->assertSet('searchQuery', '');
    }

    public function test_search_query_clears_when_another_selector_opens(): void
    {
        $user = User::factory()->create();
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        $component = Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->set('searchQuery', 'pasta')
            ->set('showSelector', true)
            ->assertSet('searchQuery', 'pasta');

        $componentId = $component->instance()->getId();

        $component->call('handleSelectorOpened', 'different-component-id')
            ->assertSet('showSelector', false)
            ->assertSet('searchQuery', '');
    }

    public function test_user_can_only_see_own_recipes_in_selector(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        Recipe::factory()->create(['user_id' => $userA->id, 'name' => 'User A Recipe']);
        Recipe::factory()->create(['user_id' => $userB->id, 'name' => 'User B Recipe']);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($userA);

        $component = Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch']);

        $recipes = $component->viewData('recipes');
        $this->assertEquals(1, $recipes->count());
        $this->assertEquals('User A Recipe', $recipes->first()->name);
    }

    public function test_selecting_recipe_dispatches_menu_updated_event(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->call('selectRecipe', $recipe->id)
            ->assertDispatched('menu-updated');
    }

    public function test_selecting_recipe_closes_selector(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->set('showSelector', true)
            ->call('selectRecipe', $recipe->id)
            ->assertSet('showSelector', false);
    }

    public function test_recipes_are_sorted_alphabetically(): void
    {
        $user = User::factory()->create();
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Zebra Steak']);
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Apple Pie']);
        Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Mango Salad']);
        $date = Carbon::now()->format('Y-m-d');

        $this->actingAs($user);

        $component = Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch']);

        $recipes = $component->viewData('recipes');
        $this->assertEquals('Apple Pie', $recipes->first()->name);
        $this->assertEquals('Zebra Steak', $recipes->last()->name);
    }
}
