<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->string('grade_file_path')->nullable()->after('rejection_reason');
            $table->text('grade_file_url')->nullable()->after('grade_file_path');
            $table->string('grade_file_name')->nullable()->after('grade_file_url');
            $table->string('grade_file_mime', 100)->nullable()->after('grade_file_name');
            $table->unsignedBigInteger('grade_file_size')->nullable()->after('grade_file_mime');

            $table->foreignId('grade_file_uploaded_by')
                ->nullable()
                ->after('grade_file_size')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('grade_file_uploaded_at')->nullable()->after('grade_file_uploaded_by');

            $table->enum('grade_file_status', ['pending_review', 'approved', 'rejected'])
                ->nullable()
                ->after('grade_file_uploaded_at');
            $table->text('grade_file_review_notes')->nullable()->after('grade_file_status');

            $table->foreignId('grade_file_reviewed_by')
                ->nullable()
                ->after('grade_file_review_notes')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('grade_file_reviewed_at')->nullable()->after('grade_file_reviewed_by');

            $table->index('grade_file_status');
            $table->index('grade_file_uploaded_by');
            $table->index('grade_file_reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->dropIndex(['grade_file_status']);
            $table->dropIndex(['grade_file_uploaded_by']);
            $table->dropIndex(['grade_file_reviewed_by']);

            $table->dropForeign(['grade_file_uploaded_by']);
            $table->dropForeign(['grade_file_reviewed_by']);

            $table->dropColumn([
                'grade_file_path',
                'grade_file_url',
                'grade_file_name',
                'grade_file_mime',
                'grade_file_size',
                'grade_file_uploaded_by',
                'grade_file_uploaded_at',
                'grade_file_status',
                'grade_file_review_notes',
                'grade_file_reviewed_by',
                'grade_file_reviewed_at',
            ]);
        });
    }
};
