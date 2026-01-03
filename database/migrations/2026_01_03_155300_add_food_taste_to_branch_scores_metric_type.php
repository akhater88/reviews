<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the ENUM to include food_taste
        DB::statement("ALTER TABLE internal_competition_branch_scores MODIFY COLUMN metric_type ENUM('customer_satisfaction', 'response_time', 'food_taste') NOT NULL");
    }

    public function down(): void
    {
        // Remove food_taste from the ENUM (will fail if there's data with food_taste)
        DB::statement("ALTER TABLE internal_competition_branch_scores MODIFY COLUMN metric_type ENUM('customer_satisfaction', 'response_time') NOT NULL");
    }
};
