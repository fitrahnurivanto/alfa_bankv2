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
        Schema::table('orders', function (Blueprint $table) {
            // Division: academy or agency
            $table->enum('division', ['agency', 'academy'])->nullable()->after('client_id');
            
            // Order type: order (with services) or registration (magang/sertifikasi)
            $table->enum('order_type', ['order', 'registration'])->default('order')->after('division');
            
            // Registration type: magang or sertifikasi (only for registration type)
            $table->enum('registration_type', ['magang', 'sertifikasi'])->nullable()->after('order_type');
            
            // Participant details for registration
            $table->string('institution_name')->nullable()->after('registration_type');
            $table->text('participant_address')->nullable()->after('institution_name');
            $table->integer('participant_age')->nullable()->after('participant_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'division',
                'order_type',
                'registration_type',
                'institution_name',
                'participant_address',
                'participant_age'
            ]);
        });
    }
};
