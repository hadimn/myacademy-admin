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

            UserProgressModel::create([
                'user_id'      => $user->id,
                'lesson_id'    => $firstLesson->lesson_id,
                'is_completed' => false,
                'time_spent'   => 0,
                'started_at'   => now(),   // only started
                'completed_at' => null,    // not completed
            ]);
        }

        echo "UserProgress seeded for FIRST lesson only (not completed).\n";
    }
}
