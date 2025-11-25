<?php

namespace Database\Seeders;

use App\Models\CoursesModel;
use App\Models\SectionsModel;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Optional: clean table before seeding
        if (SectionsModel::count() > 0) {
            SectionsModel::truncate();
        }

        $faker = Faker::create();

        $courses = CoursesModel::pluck('course_id')->toArray();

        if (empty($courses)) {
            echo "❌ No courses found. Run CoursesSeeder first.\n";
            return;
        }

        // NEW → Number of sections per course
        $sectionsPerCourse = 2;

        foreach ($courses as $courseId) {

            for ($order = 1; $order <= $sectionsPerCourse; $order++) {

                SectionsModel::create([
                    'course_id'   => $courseId,
                    'title'       => $faker->catchPhrase() . ' Section',
                    'description' => $faker->paragraph(2),
                    'image_url'   => null,
                    'order'       => $order,
                    'is_last'     => $order === $sectionsPerCourse, // last section in the course
                ]);
            }
        }

        echo "✅ Created {$sectionsPerCourse} ordered sections per course (with is_last flags).\n";
    }
}
