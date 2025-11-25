<?php

namespace Database\Seeders;

use App\Models\LessonsModel;
use App\Models\UnitsModel;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class LessonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Fetch all unit_ids
        $unitIds = UnitsModel::pluck('unit_id')->all();

        if (empty($unitIds)) {
            echo "Skipping LessonsSeeder: No units found. Run UnitsSeeder first.\n";
            return;
        }

        // Lesson types
        $lessonTypes = ['normal', 'review', 'practice'];

        foreach ($unitIds as $unitId) {

            // Make 4 lessons for each unit
            for ($order = 1; $order <= 4; $order++) {

                LessonsModel::create([
                    'unit_id'     => $unitId,
                    'title'       => "Lesson {$order}: " . $faker->words(rand(3, 6), true),
                    'description' => $faker->sentence(rand(6, 10)),
                    'content'     => $faker->randomElement([null, $faker->paragraphs(rand(2, 4), true)]),
                    'video_url'   => null,
                    'image_url'   => null,
                    'duration'    => $faker->numberBetween(5, 60),
                    'lesson_type' => $faker->randomElement($lessonTypes),
                    'chest_after' => $faker->boolean(25),
                    'order'       => $order,
                    'is_last'     => false, // updated below
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            // Mark the last lesson of this unit
            $lastLesson = LessonsModel::where('unit_id', $unitId)
                ->orderByDesc('order')
                ->first();

            if ($lastLesson) {
                $lastLesson->update(['is_last' => true]);
            }
        }

        echo "Successfully created 4 lessons for each unit with correct ordering and is_last flags.\n";
    }
}
