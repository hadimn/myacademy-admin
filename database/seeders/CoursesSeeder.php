<?php

namespace Database\Seeders;

use App\Models\coursesModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Check if the table is empty before seeding
        if (DB::table('courses')->count() > 0) {
            // Optional: Skip seeding if data already exists, or truncate if you want to clear it first.
            DB::table('courses')->truncate();
        }

        $faker = Faker::create();
        $languages = ['en', 'es', 'fr', 'de', 'ar']; // Sample languages

        // 2. Loop to create 5 courses
        for ($i = 0; $i < 5; $i++) {
            $title = $faker->sentence(3, true);

            coursesModel::create([
                'title' => $title,
                // Ensure the description is at least 26 characters long, as required by your validation
                'description' => $faker->paragraph(rand(3, 5), true),
                // Since 'video_url' and 'image_url' are nullable and store file paths
                // we'll leave them null or add a dummy path. Using null for simplicity.
                'video_url' => null,
                'image_url' => null,
                // Select a language randomly from the array
                'language' => $faker->randomElement($languages),
                // Optional: add timestamps manually if the model doesn't handle it
                // 'created_at' => now(),
                // 'updated_at' => now(),
            ]);
        }

        echo "Successfully created 5 courses.\n";
    }
}
