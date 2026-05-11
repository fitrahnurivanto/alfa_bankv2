<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add logo settings for each division
        DB::table('settings')->insert([
            [
                'key' => 'agency_logo',
                'value' => '/images/divisions/agency-logo.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'academy_logo',
                'value' => '/images/divisions/academy-logo.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', ['agency_logo', 'academy_logo'])->delete();
    }
};
