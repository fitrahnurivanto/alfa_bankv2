<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('specialization')->nullable()->after('address');
            $table->text('bio')->nullable()->after('specialization');
            $table->string('photo_path')->nullable()->after('avatar');
            $table->string('cv_path')->nullable()->after('photo_path');
            $table->string('status')->default('active')->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'specialization',
                'bio',
                'photo_path',
                'cv_path',
                'status',
            ]);
        });
    }
};
