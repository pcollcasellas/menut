<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create ingredients table
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        // Add case-insensitive unique index on household_id + name
        // Using raw SQL for cross-database compatibility
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite: COLLATE NOCASE handles case-insensitivity
            DB::statement('CREATE UNIQUE INDEX ingredients_household_name_unique ON ingredients (household_id, name COLLATE NOCASE)');
        } else {
            // MySQL: use a generated column with LOWER()
            DB::statement('ALTER TABLE ingredients ADD COLUMN name_lowercase VARCHAR(255) GENERATED ALWAYS AS (LOWER(name)) STORED');
            DB::statement('ALTER TABLE ingredients ADD UNIQUE INDEX ingredients_household_name_unique (household_id, name_lowercase)');
        }

        // Create pivot table for recipe_ingredients
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['recipe_id', 'ingredient_id']);
        });

        // Migrate existing data from recipes.ingredients text field
        $this->migrateExistingIngredients();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
        Schema::dropIfExists('ingredients');
    }

    /**
     * Migrate existing ingredients text data to the new structure.
     */
    private function migrateExistingIngredients(): void
    {
        $recipes = DB::table('recipes')
            ->whereNotNull('ingredients')
            ->where('ingredients', '!=', '')
            ->get(['id', 'household_id', 'ingredients']);

        foreach ($recipes as $recipe) {
            // Parse ingredients (one per line)
            $ingredientLines = array_filter(
                array_map('trim', explode("\n", $recipe->ingredients)),
                fn ($line) => $line !== ''
            );

            foreach ($ingredientLines as $ingredientName) {
                // Normalize the ingredient name
                $normalizedName = trim($ingredientName);
                if (empty($normalizedName)) {
                    continue;
                }

                // Find or create ingredient (case-insensitive lookup)
                $existingIngredient = DB::table('ingredients')
                    ->where('household_id', $recipe->household_id)
                    ->whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])
                    ->first();

                if ($existingIngredient) {
                    $ingredientId = $existingIngredient->id;
                } else {
                    $ingredientId = DB::table('ingredients')->insertGetId([
                        'household_id' => $recipe->household_id,
                        'name' => $normalizedName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Link ingredient to recipe (ignore duplicates)
                $exists = DB::table('recipe_ingredients')
                    ->where('recipe_id', $recipe->id)
                    ->where('ingredient_id', $ingredientId)
                    ->exists();

                if (! $exists) {
                    DB::table('recipe_ingredients')->insert([
                        'recipe_id' => $recipe->id,
                        'ingredient_id' => $ingredientId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
};
