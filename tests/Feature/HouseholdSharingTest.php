<?php

namespace Tests\Feature;

use App\Livewire\MealSlot;
use App\Livewire\RecipeManager;
use App\Livewire\TemplateManager;
use App\Livewire\WeeklyMenu;
use App\Models\MenuItem;
use App\Models\MenuTemplate;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HouseholdSharingTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_users_in_same_household_see_same_recipes(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);

        Recipe::factory()->create([
            'user_id' => $userA->id,
            'household_id' => $userA->household_id,
            'name' => 'Shared Recipe',
        ]);

        // User B should see User A's recipe
        $this->actingAs($userB);
        $component = Livewire::test(RecipeManager::class);
        $recipes = $component->viewData('recipes');

        $this->assertEquals(1, $recipes->count());
        $this->assertEquals('Shared Recipe', $recipes->first()->name);
    }

    public function test_different_households_are_isolated(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Recipe::factory()->create([
            'user_id' => $userA->id,
            'household_id' => $userA->household_id,
            'name' => 'User A Only',
        ]);

        Recipe::factory()->create([
            'user_id' => $userB->id,
            'household_id' => $userB->household_id,
            'name' => 'User B Only',
        ]);

        $this->actingAs($userA);
        $component = Livewire::test(RecipeManager::class);
        $recipes = $component->viewData('recipes');

        $this->assertEquals(1, $recipes->count());
        $this->assertEquals('User A Only', $recipes->first()->name);
    }

    public function test_recipe_created_by_one_visible_to_other(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);

        // User A creates a recipe
        $this->actingAs($userA);
        Livewire::test(RecipeManager::class)
            ->call('create')
            ->set('name', 'New Shared Recipe')
            ->set('description', 'A test recipe')
            ->call('save');

        // User B should see it
        $this->actingAs($userB);
        $component = Livewire::test(RecipeManager::class);
        $recipes = $component->viewData('recipes');

        $this->assertEquals(1, $recipes->count());
        $this->assertEquals('New Shared Recipe', $recipes->first()->name);
    }

    public function test_same_household_sees_same_menu_items(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);
        $recipe = Recipe::factory()->create([
            'user_id' => $userA->id,
            'household_id' => $userA->household_id,
        ]);
        $date = Carbon::now()->format('Y-m-d');

        // User A assigns a meal
        $this->actingAs($userA);
        Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch'])
            ->call('selectRecipe', $recipe->id);

        // User B should see it
        $this->actingAs($userB);
        $component = Livewire::test(MealSlot::class, ['date' => $date, 'mealType' => 'lunch']);
        $this->assertEquals($recipe->id, $component->get('selectedRecipeId'));
    }

    public function test_same_household_sees_same_templates(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);

        MenuTemplate::create([
            'household_id' => $userA->household_id,
            'user_id' => $userA->id,
            'name' => 'Shared Template',
        ]);

        $this->actingAs($userB);

        $component = Livewire::test(TemplateManager::class)
            ->call('open', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));

        $templates = $component->viewData('templates');
        $this->assertEquals(1, $templates->count());
        $this->assertEquals('Shared Template', $templates->first()->name);
    }

    public function test_household_member_can_edit_shared_recipe(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);

        $recipe = Recipe::factory()->create([
            'user_id' => $userA->id,
            'household_id' => $userA->household_id,
            'name' => 'Original Name',
        ]);

        $this->actingAs($userB);

        Livewire::test(RecipeManager::class)
            ->call('edit', $recipe->id)
            ->set('name', 'Updated Name')
            ->call('save');

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_household_member_can_delete_shared_recipe(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);

        $recipe = Recipe::factory()->create([
            'user_id' => $userA->id,
            'household_id' => $userA->household_id,
            'name' => 'To Delete',
        ]);

        $this->actingAs($userB);

        Livewire::test(RecipeManager::class)
            ->call('delete', $recipe->id);

        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    public function test_weekly_menu_shows_household_data(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);
        $recipe = Recipe::factory()->create([
            'user_id' => $userA->id,
            'household_id' => $userA->household_id,
            'name' => 'Monday Lunch',
        ]);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        MenuItem::create([
            'household_id' => $userA->household_id,
            'user_id' => $userA->id,
            'date' => $weekStart,
            'meal_type' => 'lunch',
            'recipe_id' => $recipe->id,
        ]);

        $this->actingAs($userB);

        $component = Livewire::test(WeeklyMenu::class);
        $menuItems = $component->viewData('menuItems');

        $key = $weekStart.'_lunch';
        $this->assertArrayHasKey($key, $menuItems);
        $this->assertEquals($recipe->id, $menuItems[$key]->recipe_id);
    }
}
