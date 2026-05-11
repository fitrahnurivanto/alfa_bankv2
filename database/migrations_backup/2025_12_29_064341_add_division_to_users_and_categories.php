<?php

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
        // Add division to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('division', ['agency', 'academy'])->nullable()->after('role');
        });

        // Add division to service_categories table
        Schema::table('service_categories', function (Blueprint $table) {
            $table->enum('division', ['agency', 'academy'])->default('agency')->after('description');
        });

        // Add division to monthly_targets table
        Schema::table('monthly_targets', function (Blueprint $table) {
            $table->enum('division', ['agency', 'academy'])->nullable()->after('month');
        });

        // Update existing service categories
        DB::table('service_categories')->where('name', 'Marketplace')->update(['division' => 'agency']);
        DB::table('service_categories')->where('name', 'Social Media')->update(['division' => 'agency']);
        DB::table('service_categories')->where('name', 'Website')->update(['division' => 'agency']);
        DB::table('service_categories')->where('name', 'Academy')->update(['division' => 'academy']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('division');
        });

        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropColumn('division');
        });

        Schema::table('monthly_targets', function (Blueprint $table) {
            $table->dropColumn('division');
        });
    }
};
