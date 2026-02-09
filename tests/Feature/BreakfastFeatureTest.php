<?php

namespace Tests\Feature;

use App\Enums\MealType;
use App\Enums\RecipeType;
use App\Livewire\MealSlot;
use App\Livewire\RecipeManager;
use App\Livewire\WeeklyMenu;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BreakfastFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_show_breakfast_defaults_to_false(): void
    {
        $user = User::factory()->create();

        // The database default is false, but factory doesn't set it explicitly
        // So we check the casted value or that it evaluates to false
        $this->assertFalse((bool) $user->show_breakfast);
    }

    public function test_user_can_enable_breakfast(): void
    {
        $user = User::factory()->create();

        $user->update(['show_breakfast' => true]);

        $this->assertTrue($user->fresh()->show_breakfast);
    }

    public function test_user_factory_has_with_breakfast_state(): void
    {
        $user = User::factory()->withBreakfast()->create();

        $this->assertTrue($user->show_breakfast);
    }

    public function test_recipe_type_defaults_to_meal(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);

        $this->assertEquals(RecipeType::Meal, $recipe->type);
    }

    public function test_recipe_factory_has_breakfast_state(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->breakfast()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);

        $this->assertEquals(RecipeType::Breakfast, $recipe->type);
    }

    public function test_recipe_scopes_filter_correctly(): void
    {
        $user = User::factory()->create();

        Recipe::factory()->count(3)->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            'type' => RecipeType::Meal,
        ]);

        Recipe::factory()->count(2)->breakfast()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
        ]);

        $this->assertEquals(3, Recipe::meal()->count());
        $this->assertEquals(2, Recipe::breakfast()->count());
    }

    public function test_weekly_menu_does_not_show_breakfast_by_default(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(WeeklyMenu::class)
            ->assertDontSee('Esmorzar');
    }

    public function test_weekly_menu_shows_breakfast_when_enabled(): void
    {
        $user = User::factory()->withBreakfast()->create();

        $this->actingAs($user);

        Livewire::test(WeeklyMenu::class)
            ->assertSee('Esmorzar');
    }

    public function test_recipe_manager_defaults_to_meal_tab(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->assertSet('recipeType', 'meal');
    }

    public function test_recipe_manager_can_switch_between_tabs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->assertSet('recipeType', 'meal')
            ->call('setRecipeType', 'breakfast')
            ->assertSet('recipeType', 'breakfast')
            ->call('setRecipeType', 'meal')
            ->assertSet('recipeType', 'meal');
    }

    public function test_recipe_manager_filters_recipes_by_type(): void
    {
        $user = User::factory()->create();

        $mealRecipe = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            'name' => 'Lunch Recipe',
            'type' => RecipeType::Meal,
        ]);

        $breakfastRecipe = Recipe::factory()->breakfast()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            'name' => 'Breakfast Recipe',
        ]);

        $this->actingAs($user);

        // On meal tab, should only see meal recipe
        Livewire::test(RecipeManager::class)
            ->assertSet('recipeType', 'meal')
            ->assertSee('Lunch Recipe')
            ->assertDontSee('Breakfast Recipe');

        // On breakfast tab, should only see breakfast recipe
        Livewire::test(RecipeManager::class)
            ->call('setRecipeType', 'breakfast')
            ->assertSee('Breakfast Recipe')
            ->assertDontSee('Lunch Recipe');
    }

    public function test_recipe_manager_creates_recipe_with_current_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        // Create a meal recipe
        Livewire::test(RecipeManager::class)
            ->call('create')
            ->set('name', 'Test Meal')
            ->call('save');

        $this->assertDatabaseHas('recipes', [
            'name' => 'Test Meal',
            'type' => RecipeType::Meal->value,
        ]);

        // Create a breakfast recipe
        Livewire::test(RecipeManager::class)
            ->call('setRecipeType', 'breakfast')
            ->call('create')
            ->set('name', 'Test Breakfast')
            ->call('save');

        $this->assertDatabaseHas('recipes', [
            'name' => 'Test Breakfast',
            'type' => RecipeType::Breakfast->value,
        ]);
    }

    public function test_meal_slot_filters_recipes_by_meal_type(): void
    {
        $user = User::factory()->create();

        $mealRecipe = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            'name' => 'Spaghetti',
            'type' => RecipeType::Meal,
        ]);

        $breakfastRecipe = Recipe::factory()->breakfast()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            'name' => 'Pancakes',
        ]);

        $this->actingAs($user);

        // Lunch slot should show meal recipes only (open selector first)
        Livewire::test(MealSlot::class, [
            'date' => now()->format('Y-m-d'),
            'mealType' => 'lunch',
        ])
            ->call('toggleSelector')
            ->assertSee('Spaghetti')
            ->assertDontSee('Pancakes');

        // Breakfast slot should show breakfast recipes only (open selector first)
        Livewire::test(MealSlot::class, [
            'date' => now()->format('Y-m-d'),
            'mealType' => 'breakfast',
        ])
            ->call('toggleSelector')
            ->assertSee('Pancakes')
            ->assertDontSee('Spaghetti');
    }

    public function test_meal_type_enum_returns_correct_recipe_type(): void
    {
        $this->assertEquals(RecipeType::Breakfast, MealType::Breakfast->recipeType());
        $this->assertEquals(RecipeType::Meal, MealType::Lunch->recipeType());
        $this->assertEquals(RecipeType::Meal, MealType::Dinner->recipeType());
    }

    public function test_meal_type_enum_has_correct_labels(): void
    {
        $this->assertEquals('Esmorzar', MealType::Breakfast->label());
        $this->assertEquals('Dinar', MealType::Lunch->label());
        $this->assertEquals('Sopar', MealType::Dinner->label());
    }

    public function test_recipe_type_enum_has_correct_labels(): void
    {
        $this->assertEquals('Esmorzar', RecipeType::Breakfast->label());
        $this->assertEquals('Àpats', RecipeType::Meal->label());
    }

    public function test_profile_page_shows_menu_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response
            ->assertOk()
            ->assertSeeVolt('profile.menu-settings-form')
            ->assertSee('Configuració del menú');
    }

    public function test_user_can_toggle_breakfast_setting(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('profile.menu-settings-form')
            ->assertSet('showBreakfast', false)
            ->set('showBreakfast', true)
            ->call('updateMenuSettings');

        $this->assertTrue($user->fresh()->show_breakfast);
    }
}
