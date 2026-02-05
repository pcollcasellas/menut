<?php

namespace Tests\Feature;

use App\Livewire\HouseholdManager;
use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_gets_household_automatically(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->household_id);
        $this->assertNotNull($user->household);
        $this->assertInstanceOf(Household::class, $user->household);
    }

    public function test_household_page_is_accessible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/household');

        $response->assertOk();
    }

    public function test_can_generate_invite_link(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Livewire::test(HouseholdManager::class)
            ->call('generateInviteLink');

        $this->assertNotNull($component->get('inviteLink'));
        $this->assertDatabaseHas('household_invitations', [
            'household_id' => $user->household_id,
            'invited_by' => $user->id,
        ]);
    }

    public function test_generating_new_link_cancels_previous(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(HouseholdManager::class)
            ->call('generateInviteLink');

        $firstInvitation = HouseholdInvitation::first();

        Livewire::test(HouseholdManager::class)
            ->call('generateInviteLink');

        $this->assertNull(HouseholdInvitation::find($firstInvitation->id));
        $this->assertEquals(1, HouseholdInvitation::where('household_id', $user->household_id)->count());
    }

    public function test_can_cancel_invitation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(HouseholdManager::class)
            ->call('generateInviteLink')
            ->call('cancelInvitation')
            ->assertSet('inviteLink', null);

        $this->assertEquals(0, HouseholdInvitation::where('household_id', $user->household_id)->count());
    }

    public function test_can_accept_invitation(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $invitation = HouseholdInvitation::create([
            'household_id' => $userA->household_id,
            'invited_by' => $userA->id,
            'token' => 'test-token-123',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($userB);

        $response = $this->post(route('household.invitation.accept', 'test-token-123'));

        $response->assertRedirect(route('dashboard'));

        $userB->refresh();
        $this->assertEquals($userA->household_id, $userB->household_id);

        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);
    }

    public function test_data_merges_on_accept(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Recipe::factory()->create([
            'user_id' => $userB->id,
            'household_id' => $userB->household_id,
            'name' => 'User B Recipe',
        ]);

        $invitation = HouseholdInvitation::create([
            'household_id' => $userA->household_id,
            'invited_by' => $userA->id,
            'token' => 'merge-token',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($userB);
        $this->post(route('household.invitation.accept', 'merge-token'));

        // User B's recipe should now be in User A's household
        $this->assertDatabaseHas('recipes', [
            'household_id' => $userA->household_id,
            'name' => 'User B Recipe',
        ]);
    }

    public function test_conflicting_menu_items_resolved_on_accept(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $date = Carbon::now()->format('Y-m-d');

        $recipeA = Recipe::factory()->create([
            'user_id' => $userA->id,
            'household_id' => $userA->household_id,
            'name' => 'Recipe A',
        ]);

        $recipeB = Recipe::factory()->create([
            'user_id' => $userB->id,
            'household_id' => $userB->household_id,
            'name' => 'Recipe B',
        ]);

        // Both have lunch on the same day
        MenuItem::create([
            'household_id' => $userA->household_id,
            'user_id' => $userA->id,
            'date' => $date,
            'meal_type' => 'lunch',
            'recipe_id' => $recipeA->id,
        ]);

        MenuItem::create([
            'household_id' => $userB->household_id,
            'user_id' => $userB->id,
            'date' => $date,
            'meal_type' => 'lunch',
            'recipe_id' => $recipeB->id,
        ]);

        $invitation = HouseholdInvitation::create([
            'household_id' => $userA->household_id,
            'invited_by' => $userA->id,
            'token' => 'conflict-token',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($userB);
        $this->post(route('household.invitation.accept', 'conflict-token'));

        // Only one menu item should exist for this slot (user A's original)
        $items = MenuItem::where('household_id', $userA->household_id)
            ->whereDate('date', $date)
            ->where('meal_type', 'lunch')
            ->get();

        $this->assertEquals(1, $items->count());
        $this->assertEquals($recipeA->id, $items->first()->recipe_id);
    }

    public function test_expired_invitation_rejected(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        HouseholdInvitation::create([
            'household_id' => $userA->household_id,
            'invited_by' => $userA->id,
            'token' => 'expired-token',
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($userB);

        $response = $this->get(route('household.invitation.show', 'expired-token'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_already_accepted_invitation_rejected(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        HouseholdInvitation::create([
            'household_id' => $userA->household_id,
            'invited_by' => $userA->id,
            'token' => 'accepted-token',
            'accepted_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($userB);

        $response = $this->post(route('household.invitation.accept', 'accepted-token'));
        $response->assertRedirect(route('dashboard'));

        $userB->refresh();
        $this->assertNotEquals($userA->household_id, $userB->household_id);
    }

    public function test_can_leave_household(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);
        $originalHouseholdId = $userA->household_id;

        $this->actingAs($userB);

        Livewire::test(HouseholdManager::class)
            ->call('leaveHousehold');

        $userB->refresh();
        $this->assertNotEquals($originalHouseholdId, $userB->household_id);

        // Original household should still exist with userA
        $this->assertNotNull(Household::find($originalHouseholdId));
        $userA->refresh();
        $this->assertEquals($originalHouseholdId, $userA->household_id);
    }

    public function test_cannot_leave_solo_household(): void
    {
        $user = User::factory()->create();
        $originalHouseholdId = $user->household_id;

        $this->actingAs($user);

        Livewire::test(HouseholdManager::class)
            ->call('leaveHousehold');

        $user->refresh();
        $this->assertEquals($originalHouseholdId, $user->household_id);
    }

    public function test_old_household_deleted_when_last_member_accepts_invite(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        $oldHouseholdId = $userB->household_id;

        $invitation = HouseholdInvitation::create([
            'household_id' => $userA->household_id,
            'invited_by' => $userA->id,
            'token' => 'delete-old-token',
            'expires_at' => now()->addDays(7),
        ]);

        $this->actingAs($userB);
        $this->post(route('household.invitation.accept', 'delete-old-token'));

        $this->assertNull(Household::find($oldHouseholdId));
    }

    public function test_household_members_displayed(): void
    {
        $userA = User::factory()->create(['name' => 'User A']);
        $userB = User::factory()->create(['name' => 'User B', 'household_id' => $userA->household_id]);

        $this->actingAs($userA);

        Livewire::test(HouseholdManager::class)
            ->assertSee('User A')
            ->assertSee('User B');
    }

    public function test_invitation_show_requires_auth(): void
    {
        $response = $this->get(route('household.invitation.show', 'some-token'));
        $response->assertRedirect(route('login'));
    }

    public function test_invitation_accept_requires_auth(): void
    {
        $response = $this->post(route('household.invitation.accept', 'some-token'));
        $response->assertRedirect(route('login'));
    }
}
