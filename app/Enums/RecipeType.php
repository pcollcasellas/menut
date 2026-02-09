<?php

namespace App\Enums;

enum RecipeType: string
{
    case Breakfast = 'breakfast';
    case Meal = 'meal';

    public function label(): string
    {
        return match ($this) {
            self::Breakfast => 'Esmorzar',
            self::Meal => 'Àpats',
        };
    }
}
