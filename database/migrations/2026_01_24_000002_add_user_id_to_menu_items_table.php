<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // Update unique constraint to include user_id
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique(['date', 'meal_type']);
            $table->unique(['user_id', 'date', 'meal_type']);
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'date', 'meal_type']);
            $table->unique(['date', 'meal_type']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
