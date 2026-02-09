<?php

namespace App\Models;

use App\Enums\RecipeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'user_id',
        'name',
        'description',
        'ingredients',
        'instructions',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => RecipeType::class,
        ];
    }

    /**
     * @param  Builder<Recipe>  $query
     * @return Builder<Recipe>
     */
    public function scopeBreakfast(Builder $query): Builder
    {
        return $query->where('type', RecipeType::Breakfast);
    }

    /**
     * @param  Builder<Recipe>  $query
     * @return Builder<Recipe>
     */
    public function scopeMeal(Builder $query): Builder
    {
        return $query->where('type', RecipeType::Meal);
    }

    /**
     * @param  Builder<Recipe>  $query
     * @return Builder<Recipe>
     */
    public function scopeOfType(Builder $query, RecipeType $type): Builder
    {
        return $query->where('type', $type);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function ingredientItems(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'recipe_ingredients')
            ->withTimestamps();
    }
}
