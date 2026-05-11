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
        Schema::table('payment_requests', function (Blueprint $table) {
            // Make project_id nullable since academy uses class_id instead
            $table->foreignId('project_id')->nullable()->change();
            
            // Add class_id for academy payment requests
            $table->foreignId('class_id')->nullable()->after('project_id')->constrained('clas')->onDelete('cascade');
            
            // Add index for class_id
            $table->index(['class_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropIndex(['class_id', 'status']);
            $table->dropColumn('class_id');
            
            // Make project_id required again
            $table->foreignId('project_id')->nullable(false)->change();
        });
    }
};
