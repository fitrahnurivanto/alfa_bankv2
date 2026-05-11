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
        Schema::table('clas', function (Blueprint $table) {
            $table->string('alamat')->nullable()->after('instansi');
            $table->string('no_pic')->nullable()->after('alamat');
            $table->string('no_kontak')->nullable()->after('no_pic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clas', function (Blueprint $table) {
            $table->dropColumn(['alamat', 'no_pic', 'no_kontak']);
        });
    }
};
