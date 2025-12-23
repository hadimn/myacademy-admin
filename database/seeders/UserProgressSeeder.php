<?php

namespace Database\Seeders;

use App\Models\LessonsModel;
use App\Models\User;
use App\Models\UserProgressModel;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class UserProgressSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            echo "No users found. Seed users first.\n";
            return;
        }

        // Get the FIRST unit → FIRST section → FIRST lesson
        $firstLesson = LessonsModel::orderBy('lesson_id')
            ->first();

        if (!$firstLesson) {
            echo "No lessons found. Seed lessons first.\n";
            return;
        }

        foreach ($users as $user) {

            // Check if already exists (avoid duplicates)
            $exists = UserProgressModel::where('user_id', $user->id)
                ->where('lesson_id', $firstLesson->lesson_id)
                ->exists();

            if ($exists) {
                continue;
            }

            $firstLesson = LessonsModel::with('unit.section.course')->orderBy('lesson_id')->first();

            UserProgressModel::create([
                'user_id'      => $user->id,
                'lesson_id'    => $firstLesson->lesson_id,
                'unit_id'      => $firstLesson->unit->unit_id,
                'section_id'   => $firstLesson->unit->section->section_id,
                'course_id'    => $firstLesson->unit->section->course->course_id,
                'is_completed' => false,
                'time_spent'   => 0,
                'started_at'   => now(),
                'completed_at' => null,
            ]);
        }

        echo "UserProgress seeded for FIRST lesson only (not completed).\n";
    }
}
