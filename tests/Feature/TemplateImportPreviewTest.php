<?php

namespace Tests\Feature;

use App\Livewire\TemplateManager;
use App\Models\MenuTemplate;
use App\Models\MenuTemplateItem;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TemplateImportPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_template_preview_contains_recipe_names(): void
    {
        $user = User::factory()->create();
        $recipeA = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Paella']);
        $recipeB = Recipe::factory()->create(['user_id' => $user->id, 'name' => 'Amanida']);

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Setmana normal',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => $recipeA->id,
            'day_of_week' => 0,
            'meal_type' => 'lunch',
        ]);

        MenuTemplateItem::create([
            'menu_template_id' => $template->id,
            'recipe_id' => $recipeB->id,
            'day_of_week' => 4,
            'meal_type' => 'dinner',
        ]);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->actingAs($user);

        $component = Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showImportForm', $template->id);

        $preview = $component->viewData('selectedTemplatePreview');

        $this->assertSame('Paella', $preview[0]['lunch']);
        $this->assertSame('Amanida', $preview[4]['dinner']);
        $this->assertNull($preview[2]['lunch']);
    }

    public function test_import_form_submits_with_livewire_action(): void
    {
        $user = User::factory()->create();

        $template = MenuTemplate::create([
            'user_id' => $user->id,
            'name' => 'Setmana normal',
        ]);

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->actingAs($user);

        Livewire::test(TemplateManager::class)
            ->call('open', $weekStart)
            ->call('showImportForm', $template->id)
            ->assertSeeHtml('wire:submit.prevent="importTemplate"');
    }
}
