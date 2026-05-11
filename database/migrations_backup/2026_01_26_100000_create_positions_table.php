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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('division', ['agency', 'academy']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial positions
        $positions = [
            // Agency
            ['name' => 'Web Developer', 'division' => 'agency'],
            ['name' => 'UI/UX Designer', 'division' => 'agency'],
            ['name' => 'Content Creator', 'division' => 'agency'],
            ['name' => 'Social Media Specialist', 'division' => 'agency'],
            ['name' => 'SEO Specialist', 'division' => 'agency'],
            ['name' => 'Graphic Designer', 'division' => 'agency'],
            ['name' => 'Digital Marketing Specialist', 'division' => 'agency'],
            ['name' => 'Project Manager', 'division' => 'agency'],
            ['name' => 'Account Executive', 'division' => 'agency'],
            ['name' => 'Customer Service', 'division' => 'agency'],
            ['name' => 'Admin', 'division' => 'agency'],
            ['name' => 'Copywriter', 'division' => 'agency'],
            ['name' => 'Video Editor', 'division' => 'agency'],
            ['name' => 'IT Support', 'division' => 'agency'],
            
            // Academy
            ['name' => 'Trainer/Instructor', 'division' => 'academy'],
            ['name' => 'Curriculum Developer', 'division' => 'academy'],
            ['name' => 'Academic Coordinator', 'division' => 'academy'],
            ['name' => 'Student Support', 'division' => 'academy'],
            ['name' => 'Content Developer', 'division' => 'academy'],
            ['name' => 'Learning Designer', 'division' => 'academy'],
            ['name' => 'Quality Assurance', 'division' => 'academy'],
            ['name' => 'Admin Academy', 'division' => 'academy'],
            ['name' => 'Marketing Academy', 'division' => 'academy'],
            ['name' => 'Customer Success Manager', 'division' => 'academy'],
        ];

        foreach ($positions as $position) {
            DB::table('positions')->insert($position);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
