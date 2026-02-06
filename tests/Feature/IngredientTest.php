<?php

namespace Tests\Feature;

use App\Livewire\RecipeManager;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingredient_is_created_when_adding_to_recipe(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->set('name', 'Test Recipe')
            ->set('selectedIngredients', ['Tomatoes', 'Onions'])
            ->call('save');

        $this->assertDatabaseHas('ingredients', [
            'household_id' => $user->household_id,
            'name' => 'Tomatoes',
        ]);

        $this->assertDatabaseHas('ingredients', [
            'household_id' => $user->household_id,
            'name' => 'Onions',
        ]);
    }

    public function test_existing_ingredient_is_reused_case_insensitive(): void
    {
        $user = User::factory()->create();
        Ingredient::create([
            'household_id' => $user->household_id,
            'name' => 'Tomatoes',
        ]);

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->set('name', 'Test Recipe')
            ->set('selectedIngredients', ['TOMATOES', 'tomatoes', 'Tomatoes'])
            ->call('save');

        // Only one ingredient should exist
        $this->assertEquals(1, Ingredient::where('household_id', $user->household_id)->count());
    }

    public function test_ingredients_are_household_scoped(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Ingredient::create([
            'household_id' => $userA->household_id,
            'name' => 'Tomatoes',
        ]);

        $this->actingAs($userB);

        Livewire::test(RecipeManager::class)
            ->set('name', 'Test Recipe')
            ->set('selectedIngredients', ['Tomatoes'])
            ->call('save');

        // Each household should have their own ingredient
        $this->assertEquals(1, Ingredient::where('household_id', $userA->household_id)->count());
        $this->assertEquals(1, Ingredient::where('household_id', $userB->household_id)->count());
    }

    public function test_recipe_displays_ingredients_as_tags(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            'name' => 'Test Recipe',
        ]);

        $ingredient1 = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Tomatoes']);
        $ingredient2 = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Onions']);
        $recipe->ingredientItems()->attach([$ingredient1->id, $ingredient2->id]);

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->assertSee('Tomatoes')
            ->assertSee('Onions');
    }

    public function test_editing_recipe_loads_ingredients(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'user_id' => $user->id,
            'household_id' => $user->household_id,
            'name' => 'Test Recipe',
        ]);

        $ingredient = Ingredient::create(['household_id' => $user->household_id, 'name' => 'Tomatoes']);
        $recipe->ingredientItems()->attach($ingredient->id);

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->call('edit', $recipe->id)
            ->assertSet('selectedIngredients', ['Tomatoes']);
    }

    public function test_can_add_ingredient_via_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->set('ingredientSearch', 'New Ingredient')
            ->call('addIngredient')
            ->assertSet('selectedIngredients', ['New Ingredient'])
            ->assertSet('ingredientSearch', '');
    }

    public function test_cannot_add_duplicate_ingredient(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->set('selectedIngredients', ['Tomatoes'])
            ->set('ingredientSearch', 'tomatoes')
            ->call('addIngredient')
            ->assertSet('selectedIngredients', ['Tomatoes']);
    }

    public function test_can_remove_ingredient(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->set('selectedIngredients', ['Tomatoes', 'Onions', 'Garlic'])
            ->call('removeIngredient', 1)
            ->assertSet('selectedIngredients', ['Tomatoes', 'Garlic']);
    }

    public function test_ingredient_suggestions_are_filtered(): void
    {
        $user = User::factory()->create();
        Ingredient::create(['household_id' => $user->household_id, 'name' => 'Tomatoes']);
        Ingredient::create(['household_id' => $user->household_id, 'name' => 'Potatoes']);
        Ingredient::create(['household_id' => $user->household_id, 'name' => 'Onions']);

        $this->actingAs($user);

        $component = Livewire::test(RecipeManager::class)
            ->set('ingredientSearch', 'ato');

        $suggestions = $component->instance()->ingredientSuggestions;
        $this->assertCount(2, $suggestions);
        $this->assertContains('Tomatoes', $suggestions);
        $this->assertContains('Potatoes', $suggestions);
    }

    public function test_ingredient_suggestions_exclude_selected(): void
    {
        $user = User::factory()->create();
        Ingredient::create(['household_id' => $user->household_id, 'name' => 'Tomatoes']);
        Ingredient::create(['household_id' => $user->household_id, 'name' => 'Potatoes']);

        $this->actingAs($user);

        $component = Livewire::test(RecipeManager::class)
            ->set('selectedIngredients', ['Tomatoes'])
            ->set('ingredientSearch', 'ato');

        $suggestions = $component->instance()->ingredientSuggestions;
        $this->assertCount(1, $suggestions);
        $this->assertContains('Potatoes', $suggestions);
    }

    public function test_can_select_suggestion(): void
    {
        $user = User::factory()->create();
        Ingredient::create(['household_id' => $user->household_id, 'name' => 'Tomatoes']);

        $this->actingAs($user);

        Livewire::test(RecipeManager::class)
            ->call('selectSuggestion', 'Tomatoes')
            ->assertSet('selectedIngredients', ['Tomatoes'])
            ->assertSet('ingredientSearch', '');
    }

    public function test_find_or_create_for_household_creates_new_ingredient(): void
    {
        $user = User::factory()->create();

        $ingredient = Ingredient::findOrCreateForHousehold($user->household_id, 'New Ingredient');

        $this->assertDatabaseHas('ingredients', [
            'household_id' => $user->household_id,
            'name' => 'New Ingredient',
        ]);
        $this->assertEquals('New Ingredient', $ingredient->name);
    }

    public function test_find_or_create_for_household_returns_existing_case_insensitive(): void
    {
        $user = User::factory()->create();
        $existing = Ingredient::create([
            'household_id' => $user->household_id,
            'name' => 'Tomatoes',
        ]);

        $ingredient = Ingredient::findOrCreateForHousehold($user->household_id, 'TOMATOES');

        $this->assertEquals($existing->id, $ingredient->id);
        $this->assertEquals(1, Ingredient::where('household_id', $user->household_id)->count());
    }
}
