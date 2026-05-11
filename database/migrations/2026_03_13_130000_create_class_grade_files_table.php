<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_grade_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('clas')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->boolean('is_active')->default(false);

            $table->string('file_path');
            $table->text('file_url');
            $table->string('file_name');
            $table->string('file_mime', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();

            $table->enum('status', ['pending_review', 'approved', 'rejected'])->default('pending_review');
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['class_id', 'version']);
            $table->index(['class_id', 'is_active']);
            $table->unique(['class_id', 'version']);
        });

        // Backfill existing single-file data into version history table.
        $classes = DB::table('clas')->whereNotNull('grade_file_url')->get();
        foreach ($classes as $class) {
            DB::table('class_grade_files')->insert([
                'class_id' => $class->id,
                'version' => 1,
                'is_active' => true,
                'file_path' => $class->grade_file_path ?? '',
                'file_url' => $class->grade_file_url,
                'file_name' => $class->grade_file_name ?? ('file-nilai-kelas-' . $class->id),
                'file_mime' => $class->grade_file_mime,
                'file_size' => $class->grade_file_size,
                'uploaded_by' => $class->grade_file_uploaded_by,
                'uploaded_at' => $class->grade_file_uploaded_at,
                'status' => $class->grade_file_status ?? 'pending_review',
                'review_notes' => $class->grade_file_review_notes,
                'reviewed_by' => $class->grade_file_reviewed_by,
                'reviewed_at' => $class->grade_file_reviewed_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_grade_files');
    }
};
