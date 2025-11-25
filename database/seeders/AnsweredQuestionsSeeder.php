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

        // Get all users and questions
        $users = User::all();
        $questions = QuestionsModel::all();

        if ($users->isEmpty() || $questions->isEmpty()) {
            echo "Skipping AnsweredQuestionsSeeder: missing users or questions.\n";
            return;
        }

        echo "Creating answered questions for {$users->count()} users and {$questions->count()} questions...\n";

        $totalRecords = 0;

        // For each user, create one answered question record for each question
        foreach ($users as $user) {
            foreach ($questions as $question) {
                // Check if this user already has a record for this question
                $existingRecord = AnsweredQuestionsModel::where('user_id', $user->id)
                    ->where('questions_id', $question->questions_id)
                    ->first();

                if (!$existingRecord) {
                    // Random pass/fail with 70% chance of passing
                    $isPassed = $faker->boolean(70);
                    
                    // If passed → full points, else → random points between 0 and points-1
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

        $expectedTotal = $users->count() * $questions->count();
        $actualTotal = AnsweredQuestionsModel::count();
        
        echo "Successfully seeded {$totalRecords} new answered question records.\n";
        echo "Total answered questions in database: {$actualTotal}\n";
        echo "Expected total (users × questions): {$expectedTotal}\n";
        
        if ($actualTotal < $expectedTotal) {
            echo "Note: Some records already existed and were skipped.\n";
        }
    }
}