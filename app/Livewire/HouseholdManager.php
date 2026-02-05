<?php

namespace App\Livewire;

use App\Livewire\Concerns\BelongsToHousehold;
use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class HouseholdManager extends Component
{
    use BelongsToHousehold;

    public ?string $inviteLink = null;

    public function generateInviteLink(): void
    {
        // Cancel any existing pending invitations from this user
        HouseholdInvitation::where('household_id', $this->householdId())
            ->where('invited_by', auth()->id())
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->delete();

        $invitation = HouseholdInvitation::create([
            'household_id' => $this->householdId(),
            'invited_by' => auth()->id(),
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        $this->inviteLink = route('household.invitation.show', $invitation->token);
    }

    public function cancelInvitation(): void
    {
        HouseholdInvitation::where('household_id', $this->householdId())
            ->where('invited_by', auth()->id())
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->delete();

        $this->inviteLink = null;
    }

    public function leaveHousehold(): void
    {
        $user = auth()->user();
        $memberCount = User::where('household_id', $this->householdId())->count();

        if ($memberCount <= 1) {
            return; // Can't leave if you're the only member
        }

        // Create a new solo household for this user
        $newHousehold = Household::create(['name' => $user->name]);
        $user->update(['household_id' => $newHousehold->id]);

        $this->redirect(route('household'), navigate: true);
    }

    public function render()
    {
        $members = User::where('household_id', $this->householdId())
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $pendingInvitation = HouseholdInvitation::where('household_id', $this->householdId())
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($pendingInvitation && ! $this->inviteLink) {
            $this->inviteLink = route('household.invitation.show', $pendingInvitation->token);
        }

        return view('livewire.household-manager', [
            'members' => $members,
            'memberCount' => $members->count(),
            'pendingInvitation' => $pendingInvitation,
        ]);
    }
}
