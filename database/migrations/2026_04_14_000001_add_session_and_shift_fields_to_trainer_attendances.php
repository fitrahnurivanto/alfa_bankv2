<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trainer_attendances', function (Blueprint $table) {
            $table->unsignedInteger('session_number')->default(1)->after('attendance_date');
            $table->time('planned_start_time')->nullable()->after('session_number');
            $table->time('shifted_start_time')->nullable()->after('planned_start_time');
            $table->text('shift_reason')->nullable()->after('shifted_start_time');
            $table->timestamp('shifted_at')->nullable()->after('shift_reason');

            $table->dropUnique('unique_daily_attendance_per_class_trainer');
            $table->unique(
                ['clas_id', 'trainer_id', 'attendance_date', 'session_number'],
                'unique_daily_attendance_per_class_trainer_session'
            );
        });
    }

    public function down(): void
    {
        Schema::table('trainer_attendances', function (Blueprint $table) {
            $table->dropUnique('unique_daily_attendance_per_class_trainer_session');
            $table->dropColumn([
                'session_number',
                'planned_start_time',
                'shifted_start_time',
                'shift_reason',
                'shifted_at',
            ]);

            $table->unique(['clas_id', 'trainer_id', 'attendance_date'], 'unique_daily_attendance_per_class_trainer');
        });
    }
};