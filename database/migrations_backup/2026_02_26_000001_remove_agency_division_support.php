<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Removes all Agency division support from the system.
     * Alfa Bank only uses Academy division.
     */
    public function up(): void
    {
        // 1. Drop division column from users table
        if (Schema::hasColumn('users', 'division')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('division');
            });
        }

        // 2. Drop division column from service_categories table
        if (Schema::hasColumn('service_categories', 'division')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->dropColumn('division');
            });
        }

        // 3. Drop division from positions table
        if (Schema::hasColumn('positions', 'division')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->dropColumn('division');
            });
        }

        // 4. Drop division from orders table
        if (Schema::hasColumn('orders', 'division')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('division');
            });
        }

        // 5. Update monthly_targets table - drop unique constraint with division, then drop column
        if (Schema::hasColumn('monthly_targets', 'division')) {
            Schema::table('monthly_targets', function (Blueprint $table) {
                // Drop old unique constraint if exists
                try {
                    $table->dropUnique(['year', 'month', 'division']);
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
                
                // Drop division column
                $table->dropColumn('division');
                
                // Add new unique constraint without division
                $table->unique(['year', 'month']);
            });
        }

        // 6. Delete Agency logo settings (only if table exists)
        if (Schema::hasTable('settings')) {
            DB::table('settings')->whereIn('key', ['logo_agency', 'company_name_agency'])->delete();
            
            // 7. Rename Academy logo to company logo (general)
            DB::table('settings')
                ->where('key', 'logo_academy')
                ->update(['key' => 'company_logo']);
                
            DB::table('settings')
                ->where('key', 'company_name_academy')
                ->update(['key' => 'company_name']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Add back division column to users
        if (!Schema::hasColumn('users', 'division')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('division', ['agency', 'academy'])->nullable()->after('role');
            });
        }

        // 2. Add back division to service_categories
        if (!Schema::hasColumn('service_categories', 'division')) {
            Schema::table('service_categories', function (Blueprint $table) {
                $table->enum('division', ['agency', 'academy'])->default('academy')->after('description');
            });
        }

        // 3. Add back division to positions
        if (!Schema::hasColumn('positions', 'division')) {
            Schema::table('positions', function (Blueprint $table) {
                $table->enum('division', ['agency', 'academy'])->after('name');
            });
        }

        // 4. Add back division to orders
        if (!Schema::hasColumn('orders', 'division')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('division', ['agency', 'academy'])->nullable()->after('client_id');
            });
        }

        // 5. Add back division to monthly_targets
        if (!Schema::hasColumn('monthly_targets', 'division')) {
            Schema::table('monthly_targets', function (Blueprint $table) {
                // Drop current unique constraint
                try {
                    $table->dropUnique(['year', 'month']);
                } catch (\Exception $e) {
                    // Ignore
                }
                
                // Add division column
                $table->enum('division', ['agency', 'academy'])->nullable()->after('month');
                
                // Add back unique constraint with division
                $table->unique(['year', 'month', 'division']);
            });
        }

        // 6. Restore settings
        DB::table('settings')
            ->where('key', 'company_logo')
            ->update(['key' => 'logo_academy']);
            
        DB::table('settings')
            ->where('key', 'company_name')
            ->update(['key' => 'company_name_academy']);
    }
};
