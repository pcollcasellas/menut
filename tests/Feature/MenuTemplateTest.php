<?php

namespace Tests\Feature;

use App\Livewire\TemplateManager;
use App\Models\MenuItem;
use App\Models\MenuTemplate;
use App\Models\MenuTemplateItem;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_menu_template(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        MenuItem::create([
            'user_id' => $user->id,
            'date' => $weekStart,
            'meal_type' => 'lunch',
            'recipe_id' => $recipe->id,
        ]);

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->set('templateName', 'Menu setmanal')
            ->call('saveTemplate');

        $this->assertDatabaseHas('menu_templates', [
            'user_id' => $user->id,
            'name' => 'Menu setmanal',
        ]);

        $this->assertDatabaseHas('menu_template_items', [
            'day_of_week' => 0,
            'meal_type' => 'lunch',
            'recipe_id' => $recipe->id,
        ]);
    }

    public function test_can_import_template_to_empty_week(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Test Template',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => $recipe->id,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showImportForm', $template->id)
            ->set('importMode', 'skip')
            ->call('importTemplate');

        $menuItem = MenuItem::where('user_id', $user->id)
            ->where('meal_type', 'lunch')
            ->whereDate('date', $weekStart)
            ->first();

        $this->assertNotNull($menuItem);
        $this->assertEquals($recipe->id, $menuItem->recipe_id);
    }

    public function test_import_with_skip_mode_preserves_existing(): void
    {
        $user = User::factory()->create();
        $recipeA = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Recipe A']);
        $recipeB = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Recipe B']);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        MenuItem::create([
            'user_id' => $user->id,
            'date' => $weekStart,
            'meal_type' => 'lunch',
            'recipe_id' => $recipeA->id,
        ]);

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Test Template',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => $recipeB->id,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showImportForm', $template->id)
            ->set('importMode', 'skip')
            ->call('importTemplate');

        $menuItem = MenuItem::where('user_id', $user->id)
            ->where('meal_type', 'lunch')
            ->whereDate('date', $weekStart)
            ->first();

        $this->assertNotNull($menuItem);
        $this->assertEquals($recipeA->id, $menuItem->recipe_id);
    }

    public function test_import_with_replace_mode_overwrites_existing(): void
    {
        $user = User::factory()->create();
        $recipeA = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Recipe A']);
        $recipeB = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Recipe B']);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        MenuItem::create([
            'user_id' => $user->id,
            'date' => $weekStart,
            'meal_type' => 'lunch',
            'recipe_id' => $recipeA->id,
        ]);

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Test Template',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => $recipeB->id,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showImportForm', $template->id)
            ->set('importMode', 'replace')
            ->call('importTemplate');

        $menuItem = MenuItem::where('user_id', $user->id)
            ->where('meal_type', 'lunch')
            ->whereDate('date', $weekStart)
            ->first();

        $this->assertNotNull($menuItem);
        $this->assertEquals($recipeB->id, $menuItem->recipe_id);
    }

    public function test_handles_deleted_recipes_on_import(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Test Template',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => null, // Simulates deleted recipe
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showImportForm', $template->id)
            ->set('importMode', 'skip')
            ->call('importTemplate');

        $this->assertDatabaseMissing('menu_items', [
            'user_id' => $user->id,
            'date' => $weekStart,
            'meal_type' => 'lunch',
        ]);
    }

    public function test_can_delete_template(): void
    {
        $user = User::factory()->create();

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Test Template',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => null,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('deleteTemplate', $template->id);

        $this->assertDatabaseMissing('menu_templates', [
            'id' => $template->id,
        ]);

        $this->assertDatabaseMissing('menu_template_items', [
            'menu_template_id' => $template->id,
        ]);
    }

    public function test_can_create_multiple_templates(): void
    {
        $user = User::factory()->create();
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->set('templateName', 'Template 1')
            ->call('saveTemplate');

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->set('templateName', 'Template 2')
            ->call('saveTemplate');

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->set('templateName', 'Template 3')
            ->call('saveTemplate');

        $this->assertEquals(3, MenuTemplate::where('user_id', $user->id)->count());
    }

    public function test_user_can_only_see_own_templates(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        MenuTemplate::create(['user_id' => $userA->id, 'name' => 'User A Template']);
        MenuTemplate::create(['user_id' => $userB->id, 'name' => 'User B Template']);

        $this->actingAs($userA);

        $component = Livewire::test(TemplateManager::class)
            ->call('open', Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));

        $templates = $component->viewData('templates');

        $this->assertEquals(1, $templates->count());
        $this->assertEquals('User A Template', $templates->first()->name);
    }

    public function test_user_can_only_see_own_recipes(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Recipe::factory()->create(['user_id' => $userA->id, 'name' => 'User A Recipe']);
        Recipe::factory()->create(['user_id' => $userB->id, 'name' => 'User B Recipe']);

        $recipes = Recipe::where('user_id', $userA->id)->get();

        $this->assertEquals(1, $recipes->count());
        $this->assertEquals('User A Recipe', $recipes->first()->name);
    }

    public function test_user_can_only_see_own_menu_items(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $recipeA = Recipe::factory()->create(['user_id' => $userA->id]);
        $recipeB = Recipe::factory()->create(['user_id' => $userB->id]);

        $date = Carbon::now()->format('Y-m-d');

        MenuItem::create([
            'user_id' => $userA->id,
            'date' => $date,
            'meal_type' => 'lunch',
            'recipe_id' => $recipeA->id,
        ]);

        MenuItem::create([
            'user_id' => $userB->id,
            'date' => $date,
            'meal_type' => 'lunch',
            'recipe_id' => $recipeB->id,
        ]);

        $items = MenuItem::where('user_id', $userA->id)->get();

        $this->assertEquals(1, $items->count());
        $this->assertEquals($recipeA->id, $items->first()->recipe_id);
    }

    public function test_can_edit_template_slot(): void
    {
        $user = User::factory()->create();
        $recipeA = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Recipe A']);
        $recipeB = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Recipe B']);

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Test Template',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => $recipeA->id,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showEditForm', $template->id)
            ->assertSet('mode', 'edit')
            ->assertSet('editingSlots.0_lunch', $recipeA->id)
            ->call('updateSlot', '0_lunch', $recipeB->id)
            ->assertSet('editingSlots.0_lunch', $recipeB->id)
            ->call('saveTemplateChanges');

        $this->assertDatabaseHas('menu_template_items', [
            'menu_template_id' => $template->id,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
            'recipe_id' => $recipeB->id,
        ]);
    }

    public function test_can_clear_template_slot(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create(['user_id' => $user->id]);

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Test Template',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => $recipe->id,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showEditForm', $template->id)
            ->assertSet('editingSlots.0_lunch', $recipe->id)
            ->call('updateSlot', '0_lunch', null)
            ->assertSet('editingSlots.0_lunch', null)
            ->call('saveTemplateChanges');

        $this->assertDatabaseMissing('menu_template_items', [
            'menu_template_id' => $template->id,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);
    }

    public function test_can_rename_template(): void
    {
        $user = User::factory()->create();

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Original Name',
        ]);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showEditForm', $template->id)
            ->assertSet('editingTemplateName', 'Original Name')
            ->set('editingTemplateName', 'New Name')
            ->call('saveTemplateChanges');

        $this->assertDatabaseHas('menu_templates', [
            'id' => $template->id,
            'name' => 'New Name',
        ]);
    }
}
