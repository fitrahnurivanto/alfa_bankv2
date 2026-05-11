<?php

namespace App\Console\Commands;

use App\Models\Training;
use App\Models\Clas;
use App\Models\Kategori;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateDummyData extends Command
{
    protected $signature = 'dummy:create';
    protected $description = 'Create dummy training and class data for testing';

    public function handle()
    {
        // Get categories
        $regular = Kategori::where('nama_kategori', 'like', '%regular%')->first();
        $corporate = Kategori::where('nama_kategori', 'like', '%corporate%')->first();
        $private = Kategori::where('nama_kategori', 'like', '%private%')->first();

        if (!$regular || !$corporate || !$private) {
            $this->error('Kategori not found!');
            return 1;
        }

        // Create Regular trainings
        $trainings_regular = [
            ['name' => 'Laravel Dasar', 'type' => 'reguler'],
            ['name' => 'React JS', 'type' => 'reguler'],
            ['name' => 'Vue JS', 'type' => 'reguler'],
            ['name' => 'Angular', 'type' => 'reguler'],
        ];

        foreach ($trainings_regular as $t) {
            Training::firstOrCreate(
                ['name' => $t['name']],
                ['slug' => Str::slug($t['name']), 'type' => $t['type']]
            );
            $this->info("Created training: {$t['name']}");
        }

        // Create Corporate trainings
        $trainings_corp = [
            ['name' => 'Java Advanced', 'type' => 'corporate'],
            ['name' => 'Python Enterprise', 'type' => 'corporate'],
            ['name' => 'Go Lang', 'type' => 'corporate'],
        ];

        foreach ($trainings_corp as $t) {
            Training::firstOrCreate(
                ['name' => $t['name']],
                ['slug' => Str::slug($t['name']), 'type' => $t['type']]
            );
            $this->info("Created training: {$t['name']}");
        }

        // Create Private trainings
        Training::firstOrCreate(
            ['name' => 'Custom PHP Training'],
            ['slug' => 'custom-php-training', 'type' => 'private']
        );
        $this->info("Created training: Custom PHP Training");

        // Create classes with passed_students
        $classes_data = [
            ['regular', 'Laravel Dasar', 5, 4, 1],
            ['regular', 'React JS', 3, 3, 0],
            ['regular', 'Vue JS', 4, 2, 2],
            ['regular', 'Angular', 2, 0, 2],
            ['corporate', 'Java Advanced', 8, 7, 1],
            ['corporate', 'Python Enterprise', 5, 4, 1],
            ['corporate', 'Go Lang', 3, 2, 1],
            ['private', 'Custom PHP Training', 1, 1, 0],
            ['private', 'Custom PHP Training', 1, 1, 0],
        ];

        foreach ($classes_data as [$cat, $train_name, $amount, $passed, $failed]) {
            $category = $cat === 'regular' ? $regular : ($cat === 'corporate' ? $corporate : $private);
            $training = Training::where('name', $train_name)->first();
            
            if ($category && $training) {
                $className = ucfirst($cat) . ' ' . substr($train_name, 0, 10) . ' ' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                
                Clas::create([
                    'name' => $className,
                    'slug' => Str::slug($className),
                    'kategori_id' => $category->id,
                    'training_id' => $training->id,
                    'amount' => $amount,
                    'meet' => 10,
                    'income' => $amount * 500000,
                    'status' => 'done',
                    'passed_students' => $passed,
                    'failed_students' => $failed,
                    'start_date' => now()->subMonths(2),
                    'end_date' => now()->subMonth(),
                    'done_at' => now()->subMonth(),
                ]);
                
                $this->info("Created class: {$className}");
            }
        }

        $this->info('✓ Dummy data created successfully!');
        return 0;
    }
}
