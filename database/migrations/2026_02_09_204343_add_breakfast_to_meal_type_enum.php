<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert meal_type from enum to string for better database portability.
     * Validation of allowed values is handled at the application level.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('meal_type', 20)->change();
        });

        Schema::table('menu_template_items', function (Blueprint $table) {
            $table->string('meal_type', 20)->change();
        });
    }

    public function down(): void
    {
        // Note: Converting back to enum requires data to match allowed values
        Schema::table('menu_items', function (Blueprint $table) {
            $table->enum('meal_type', ['lunch', 'dinner'])->change();
        });

        Schema::table('menu_template_items', function (Blueprint $table) {
            $table->enum('meal_type', ['lunch', 'dinner'])->change();
        });
    }
};
