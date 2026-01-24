<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuTemplateItem extends Model
{
    protected $fillable = [
        'menu_template_id',
        'recipe_id',
        'day_of_week',
        'meal_type',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MenuTemplate::class, 'menu_template_id');
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
