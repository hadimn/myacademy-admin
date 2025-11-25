<?php

namespace Database\Seeders;

use App\Models\BadgesModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BadgesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Define the CORE LOGIC (Criteria, Base Names, Points, and now TYPE) statically
        $staticBadges = [
            // Criteria: lessons_completed -> Completion
            ['name_base' => 'The Starter', 'criteria' => ['type' => 'lessons_completed', 'value' => 1], 'points' => 10, 'type' => 'completion'],

            // Criteria: courses_enrolled -> Milestone (or completion of the enrollment step)
            ['name_base' => 'Course Enthusiast', 'criteria' => ['type' => 'courses_enrolled', 'value' => 3], 'points' => 25, 'type' => 'milestone'],

            // Criteria: perfect_quizzes -> Performance
            ['name_base' => 'Quiz Whiz', 'criteria' => ['type' => 'perfect_quizzes', 'value' => 5], 'points' => 50, 'type' => 'performance'],

            // Criteria: time_spent_seconds -> Milestone (reaching a time goal)
            ['name_base' => 'Marathon Learner', 'criteria' => ['type' => 'time_spent_seconds', 'value' => 3600], 'points' => 40, 'type' => 'milestone'],

            // Criteria: section_completion -> Completion
            ['name_base' => 'Section Master I', 'criteria' => ['type' => 'section_completion', 'value' => 1], 'points' => 75, 'type' => 'completion'],

            // Criteria: lessons_completed -> Completion
            ['name_base' => 'Lesson Pro', 'criteria' => ['type' => 'lessons_completed', 'value' => 20], 'points' => 100, 'type' => 'completion'],

            // Criteria: paid_course_enrollment -> Milestone
            ['name_base' => 'First Pay', 'criteria' => ['type' => 'paid_course_enrollment', 'value' => 1], 'points' => 50, 'type' => 'milestone'],

            // Criteria: profile_complete -> Milestone
            ['name_base' => 'Community Member', 'criteria' => ['type' => 'profile_complete', 'value' => 1], 'points' => 20, 'type' => 'milestone'],

            // Criteria: courses_completed -> Completion
            ['name_base' => 'Top Student', 'criteria' => ['type' => 'courses_completed', 'value' => 1], 'points' => 500, 'type' => 'completion'],
        ];

        // 2. Loop through the static criteria and use Faker for random flavor
        foreach ($staticBadges as $data) {

            // Use Faker to generate a random description that sounds meaningful
            $description = $data['name_base'] . ': ' . $faker->optional(0.7)->sentence(6, true);

            BadgesModel::create([
                'name'          => $data['name_base'],
                'description'   => $description,

                // ICON is set to NULL as requested
                'icon'          => null,

                // 💥 FIX: Inject the required 'type' field
                'type'          => $data['type'],

                // Criteria remains static and is converted to JSON
                'criteria'      => json_encode($data['criteria']),
                'points'        => $data['points'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        echo "Successfully created " . count($staticBadges) . " initial badges with randomized descriptions and null icons.\n";
    }
}
