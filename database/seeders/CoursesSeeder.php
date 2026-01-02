<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoursesModel;
use Faker\Factory as Faker;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $levels = ['beginner', 'intermediate', 'advanced'];
        $languages = ['En', 'Ar', 'Fr'];

        $imagePath = 'uploads/course/image_url/0EWRtW2BpHjq3OiRkBRJ8YYsa8HENNglM8OMU3w5.png';

        for ($i = 1; $i <= 5; $i++) {
            CoursesModel::create([
                'title' => ucfirst($faker->words(3, true)),
                'description' => $faker->paragraphs(3, true), // > 26 chars
                'level' => $faker->randomElement($levels),
                'topics' => json_encode([
                    ucfirst($faker->word),
                    ucfirst($faker->word),
                    ucfirst($faker->word),
                    ucfirst($faker->word),
                ]),
                'video_url' => null,
                'image_url' => $imagePath,
                'language' => $faker->randomElement($languages),
                'order' => $i,
            ]);
        }
    }
}
