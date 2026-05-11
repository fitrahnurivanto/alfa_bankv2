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
        // Add fields to clients table
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'business_type')) {
                $table->string('business_type')->nullable()->after('company_name');
            }
            if (!Schema::hasColumn('clients', 'referral_source')) {
                $table->string('referral_source')->nullable()->after('address');
            }
        });

        // Add fields to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'pks_number')) {
                $table->string('pks_number')->nullable()->after('order_number');
            }
            if (!Schema::hasColumn('orders', 'payment_notes')) {
                $table->text('payment_notes')->nullable()->after('remaining_amount');
            }
        });

        // Add fields to projects table
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'duration')) {
                $table->string('duration')->nullable()->after('end_date'); // "1 Bulan", "3 Hari", etc
            }
            if (!Schema::hasColumn('projects', 'pic_internal')) {
                $table->string('pic_internal')->nullable()->after('status'); // Person in charge internal
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['business_type', 'referral_source']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pks_number', 'payment_notes']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['duration', 'pic_internal']);
        });
    }
};
