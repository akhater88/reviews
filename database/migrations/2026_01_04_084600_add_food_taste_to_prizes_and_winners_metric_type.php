<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add food_taste to internal_competition_prizes metric_type enum
        DB::statement("ALTER TABLE internal_competition_prizes MODIFY COLUMN metric_type ENUM('employee_mentions', 'customer_satisfaction', 'response_time', 'food_taste') NOT NULL");

        // Add food_taste to internal_competition_winners metric_type enum
        DB::statement("ALTER TABLE internal_competition_winners MODIFY COLUMN metric_type ENUM('employee_mentions', 'customer_satisfaction', 'response_time', 'food_taste') NOT NULL");
    }

    public function down(): void
    {
        // Remove food_taste from the ENUMs (will fail if there's data with food_taste)
        DB::statement("ALTER TABLE internal_competition_prizes MODIFY COLUMN metric_type ENUM('employee_mentions', 'customer_satisfaction', 'response_time') NOT NULL");
        DB::statement("ALTER TABLE internal_competition_winners MODIFY COLUMN metric_type ENUM('employee_mentions', 'customer_satisfaction', 'response_time') NOT NULL");
    }
};
