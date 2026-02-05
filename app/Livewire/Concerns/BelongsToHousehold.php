<?php

namespace App\Livewire\Concerns;

trait BelongsToHousehold
{
    protected function householdId(): int
    {
        return auth()->user()->household_id;
    }
}
