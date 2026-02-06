<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'name',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_ingredients')
            ->withTimestamps();
    }

    /**
     * Find or create an ingredient by name for a household (case-insensitive).
     */
    public static function findOrCreateForHousehold(int $householdId, string $name): self
    {
        $normalizedName = trim($name);

        $ingredient = static::where('household_id', $householdId)
            ->whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])
            ->first();

        if (! $ingredient) {
            $ingredient = static::create([
                'household_id' => $householdId,
                'name' => $normalizedName,
            ]);
        }

        return $ingredient;
    }
}
