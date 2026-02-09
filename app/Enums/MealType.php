<?php

namespace App\Enums;

enum MealType: string
{
    case Breakfast = 'breakfast';
    case Lunch = 'lunch';
    case Dinner = 'dinner';

    public function label(): string
    {
        return match ($this) {
            self::Breakfast => 'Esmorzar',
            self::Lunch => 'Dinar',
            self::Dinner => 'Sopar',
        };
    }

    /**
     * Get the recipe type that should be used for this meal type.
     */
    public function recipeType(): RecipeType
    {
        return match ($this) {
            self::Breakfast => RecipeType::Breakfast,
            self::Lunch, self::Dinner => RecipeType::Meal,
        };
    }

    /**
     * Check if this meal type uses breakfast recipes.
     */
    public function isBreakfast(): bool
    {
        return $this === self::Breakfast;
    }
}
