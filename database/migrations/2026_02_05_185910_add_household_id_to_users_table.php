<?php

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable()->constrained()->cascadeOnDelete();
        });

        // Backfill: create a household for each existing user
        User::whereNull('household_id')->each(function (User $user) {
            $household = Household::create(['name' => $user->name]);
            $user->update(['household_id' => $household->id]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('household_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
            $table->dropColumn('household_id');
        });
    }
};
