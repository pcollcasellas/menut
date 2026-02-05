<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response
            ->assertOk()
            ->assertSeeVolt('profile.update-profile-information-form')
            ->assertSeeVolt('profile.update-password-form')
            ->assertSeeVolt('profile.delete-user-form');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($user->fresh());
    }

    public function test_deleting_last_member_deletes_household_and_data(): void
    {
        $user = User::factory()->create();
        $householdId = $user->household_id;
        Recipe::factory()->create(['user_id' => $user->id, 'household_id' => $householdId]);

        $this->actingAs($user);

        Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $this->assertNull(Household::find($householdId));
        $this->assertDatabaseMissing('recipes', ['household_id' => $householdId]);
    }

    public function test_deleting_user_with_other_household_members_preserves_data(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create(['household_id' => $userA->household_id]);
        $householdId = $userA->household_id;

        Recipe::factory()->create(['user_id' => $userA->id, 'household_id' => $householdId, 'name' => 'Shared Recipe']);

        $this->actingAs($userA);

        Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        // Household and data should persist
        $this->assertNotNull(Household::find($householdId));
        $this->assertDatabaseHas('recipes', [
            'household_id' => $householdId,
            'name' => 'Shared Recipe',
        ]);
        // user_id should be null since user was deleted
        $recipe = Recipe::where('household_id', $householdId)->first();
        $this->assertNull($recipe->user_id);
    }
}
