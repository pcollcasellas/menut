<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Backfill household_id from user
        DB::table('menu_items')
            ->whereNull('household_id')
            ->whereNotNull('user_id')
            ->update(['household_id' => DB::raw('(SELECT household_id FROM users WHERE users.id = menu_items.user_id)')]);

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable(false)->change();
        });

        // Change unique constraint from user_id-based to household_id-based
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'date', 'meal_type']);
            $table->unique(['household_id', 'date', 'meal_type']);
        });

        // Change user_id FK from CASCADE to SET NULL
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique(['household_id', 'date', 'meal_type']);
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'date', 'meal_type']);
            $table->dropForeign(['household_id']);
            $table->dropColumn('household_id');
        });
    }
};
