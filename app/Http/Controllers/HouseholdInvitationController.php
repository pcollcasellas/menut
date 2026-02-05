<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\HouseholdInvitation;
use App\Models\MenuItem;
use App\Models\MenuTemplate;
use App\Models\Recipe;
use Illuminate\Support\Facades\DB;

class HouseholdInvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = HouseholdInvitation::where('token', $token)
            ->with(['household', 'inviter'])
            ->firstOrFail();

        if (! $invitation->isPending()) {
            return redirect()->route('dashboard')
                ->with('error', 'Aquesta invitació ja no és vàlida.');
        }

        // Don't show if user is already in the target household
        if (auth()->user()->household_id === $invitation->household_id) {
            return redirect()->route('dashboard')
                ->with('info', 'Ja formes part d\'aquesta llar.');
        }

        return view('household.invitation', compact('invitation'));
    }

    public function accept(string $token)
    {
        $invitation = HouseholdInvitation::where('token', $token)
            ->with('household')
            ->firstOrFail();

        if (! $invitation->isPending()) {
            return redirect()->route('dashboard')
                ->with('error', 'Aquesta invitació ja no és vàlida.');
        }

        $user = auth()->user();
        $oldHouseholdId = $user->household_id;
        $newHouseholdId = $invitation->household_id;

        if ($oldHouseholdId === $newHouseholdId) {
            return redirect()->route('dashboard')
                ->with('info', 'Ja formes part d\'aquesta llar.');
        }

        DB::transaction(function () use ($user, $oldHouseholdId, $newHouseholdId, $invitation) {
            // Delete conflicting menu items (same date+meal_type already in target household)
            $conflictingSlots = MenuItem::where('household_id', $newHouseholdId)
                ->select('date', 'meal_type')
                ->get();

            foreach ($conflictingSlots as $slot) {
                MenuItem::where('household_id', $oldHouseholdId)
                    ->where('user_id', $user->id)
                    ->whereDate('date', $slot->date)
                    ->where('meal_type', $slot->meal_type)
                    ->delete();
            }

            // Move user's recipes to new household
            Recipe::where('household_id', $oldHouseholdId)
                ->update(['household_id' => $newHouseholdId]);

            // Move user's remaining menu items to new household
            MenuItem::where('household_id', $oldHouseholdId)
                ->update(['household_id' => $newHouseholdId]);

            // Move user's templates to new household
            MenuTemplate::where('household_id', $oldHouseholdId)
                ->update(['household_id' => $newHouseholdId]);

            // Update user's household
            $user->update(['household_id' => $newHouseholdId]);

            // Delete old empty household if no users remain
            $remainingUsers = \App\Models\User::where('household_id', $oldHouseholdId)->count();
            if ($remainingUsers === 0) {
                Household::find($oldHouseholdId)?->delete();
            }

            // Mark invitation as accepted
            $invitation->update(['accepted_at' => now()]);
        });

        return redirect()->route('dashboard')
            ->with('success', 'T\'has unit a la llar correctament!');
    }
}
