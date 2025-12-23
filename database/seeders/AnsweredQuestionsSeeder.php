<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\QuestionsModel;
use App\Models\AnsweredQuestionsModel;
use Faker\Factory as Faker;

class AnsweredQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all users
        $users = User::all();
        
        // LIMIT: Only get the first 2 questions
        $questions = QuestionsModel::orderBy('questions_id', 'asc')->take(2)->get();

        if ($users->isEmpty() || $questions->isEmpty()) {
            echo "Skipping AnsweredQuestionsSeeder: missing users or questions.\n";
            return;
        }

        echo "Creating answered questions for {$users->count()} users (2 questions each)...\n";

        $totalRecords = 0;

        foreach ($users as $user) {
            foreach ($questions as $question) {
                // Check if this user already has a record for this specific question
                $existingRecord = AnsweredQuestionsModel::where('user_id', $user->id)
                    ->where('questions_id', $question->questions_id)
                    ->first();

                if (!$existingRecord) {
                    $isPassed = $faker->boolean(70);
                    $earnedPoints = $isPassed ? $question->points : $faker->numberBetween(0, $question->points - 1);

                    AnsweredQuestionsModel::create([
                        'user_id'       => $user->id,
                        'questions_id'   => $question->questions_id,
                        'earned_points' => $earnedPoints,
                        'is_passed'     => $earnedPoints === $question->points,
                        'created_at'    => $faker->dateTimeBetween('-3 months', 'now'),
                        'updated_at'    => now(),
                    ]);
                    
                    $totalRecords++;
                }
            }
        }

        echo "Successfully seeded {$totalRecords} new records.\n";
    }
}