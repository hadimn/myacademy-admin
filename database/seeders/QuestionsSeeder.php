<?php

namespace Database\Seeders;

use App\Models\LessonsModel;
use App\Models\QuestionsModel;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Get all existing lesson_id values
        $lessonIds = LessonsModel::pluck('lesson_id')->all();

        if (empty($lessonIds)) {
            echo "Skipping QuestionsSeeder: No lessons found. Run LessonsSeeder first.\n";
            return;
        }

        echo "Creating 5 questions for each of " . count($lessonIds) . " lessons...\n";

        // Create exactly 5 questions for each lesson
        foreach ($lessonIds as $lessonId) {

            for ($order = 1; $order <= 5; $order++) {

                // Random question type
                $type = $faker->randomElement(['mcq', 'fill', 'torf']);

                $questionTitle = "";
                $options = [];
                $correctAnswer = [];

                switch ($type) {

                    case 'mcq':
                        $questionTitle = "Which of the following is the capital of " . $faker->city() . "?";
                        $correctOption = $faker->country();

                        $options = [
                            ['text' => $correctOption, 'is_correct' => true],
                            ['text' => $faker->country(), 'is_correct' => false],
                            ['text' => $faker->country(), 'is_correct' => false],
                            ['text' => $faker->country(), 'is_correct' => false],
                        ];

                        shuffle($options);
                        $correctAnswer = [$correctOption];
                        break;

                    case 'fill':
                        $blankWord = $faker->word();
                        $questionTitle = "The largest star in the sky is the [BLANK], which is critical for life on Earth.";
                        $correctAnswer = [$blankWord];
                        break;

                    case 'torf':
                        $isTrue = $faker->boolean();
                        $questionTitle = $faker->sentence(8) . " (True or False)";
                        $correctAnswer = [$isTrue ? 'True' : 'False'];
                        $options = [
                            ['text' => 'True'],
                            ['text' => 'False']
                        ];
                        break;
                }

                // Determine if this is the last question for this lesson
                $isLast = ($order === 5);

                // Insert question
                QuestionsModel::create([
                    'lesson_id'       => $lessonId,
                    'title'           => $questionTitle,
                    'description'     => $faker->randomElement([null, $faker->sentence()]),
                    'question_type'   => $type,
                    'video_url'       => null,
                    'image_url'       => null,
                    'points'          => $faker->numberBetween(1, 5),
                    'is_last'         => $isLast,
                    'options'         => json_encode($options),
                    'correct_answer'  => json_encode($correctAnswer),
                    'explanation'     => $faker->randomElement([null, $faker->sentence(15)]),
                    'order'           => $order,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                echo "Created question $order/5 for lesson $lessonId\n";
            }
        }

        // Verify is_last flags
        $lessonsWithQuestions = LessonsModel::whereHas('questions')->get();

        foreach ($lessonsWithQuestions as $lesson) {
            $lastQuestion = QuestionsModel::where('lesson_id', $lesson->lesson_id)
                ->orderByDesc('order')
                ->first();

            if ($lastQuestion && !$lastQuestion->is_last) {
                $lastQuestion->update(['is_last' => true]);
                echo "Fixed is_last flag for lesson $lesson->lesson_id\n";
            }
        }

        $totalQuestions = QuestionsModel::count();
        echo "Successfully created $totalQuestions questions (5 per lesson) with correct ordering and is_last flags.\n";

        // Summary
        $questionsPerLesson = QuestionsModel::select('lesson_id')
            ->selectRaw('COUNT(*) as question_count')
            ->groupBy('lesson_id')
            ->get();

        echo "Questions distribution:\n";
        foreach ($questionsPerLesson as $item) {
            echo "Lesson $item->lesson_id: $item->question_count questions\n";
        }
    }
}
