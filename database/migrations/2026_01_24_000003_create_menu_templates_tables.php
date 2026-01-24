<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('menu_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipe_id')->nullable()->constrained()->onDelete('set null');
            $table->tinyInteger('day_of_week'); // 0=Monday, 6=Sunday
            $table->enum('meal_type', ['lunch', 'dinner']);
            $table->timestamps();

            $table->unique(['menu_template_id', 'day_of_week', 'meal_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_template_items');
        Schema::dropIfExists('menu_templates');
    }
};
