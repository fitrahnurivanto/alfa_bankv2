<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep legacy keys while allowing newer operational categories used by the app.
        DB::statement("\n            ALTER TABLE class_expenses\n            MODIFY COLUMN category ENUM(\n                'honor',\n                'transport',\n                'meal',\n                'accommodation',\n                'equipment',\n                'marketing',\n                'venue_rent',\n                'electricity',\n                'goodie_bag',\n                'other',\n                'operational_cost',\n                'trainer_honor',\n                'akomodasi',\n                'konsumsi',\n                'peralatan',\n                'lain-lain'\n            ) NOT NULL DEFAULT 'other'\n        ");
    }

    public function down(): void
    {
        // Normalize incompatible values before shrinking enum back.
        DB::statement("\n            UPDATE class_expenses\n            SET category = 'lain-lain'\n            WHERE category IS NULL OR category NOT IN ('lain-lain', 'honor')\n        ");

        DB::statement("\n            ALTER TABLE class_expenses\n            MODIFY COLUMN category ENUM('lain-lain', 'honor') NOT NULL DEFAULT 'lain-lain'\n        ");
    }
};
