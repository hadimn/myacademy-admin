<?php

namespace App\Http\Controllers;

use App\Models\AnsweredQuestionsModel;
use App\Models\LessonsModel;
use App\Models\QuestionsModel;
use App\Models\SectionsModel;
use App\Models\UserProgressModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProgressController extends BaseCrudController
{
    public function __construct()
    {
        $this->model = UserProgressModel::class;
        $this->resourceName = 'User Progress';
        $this->validationRules = [
            'user_id' => 'required|integer|exists:users,id',
            'lesson_id' => 'required|integer|exists:lessons,lesson_id',
            'is_completed' => 'nullable|boolean',
            'time_spent' => 'nullable|integer|min:0',
            'points' => 'nullable|integer|min:10|max:10',
            'started_at' => 'required|date|after_or_equal:today',
            'completed_at' => 'required|date|after_or_equal:today',
        ];

        $this->editValidationRules = [
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'lesson_id' => 'sometimes|required|integer|exists:lessons,lesson_id',
            'is_completed' => 'nullable|boolean',
            'time_spent' => 'nullable|integer|min:0',
            'points' => 'nullable|integer|min:10|max:10',
            'started_at' => 'sometimes|required|date|after_or_equal:today',
            'completed_at' => 'sometimes|required|date|after_or_equal:today',
        ];
    }

    public function addPointsForCorrectAnswers(Request $request, $questionId)
    {
        // 1️⃣ Validate request
        $request->validate([
            'correct_answer' => 'required|json',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        // 2️⃣ Find question
        $question = QuestionsModel::find($questionId);
        if (!$question) {
            return response()->json(['status' => 'failed', 'message' => 'Question not found']);
        }

        $userAnswer = json_decode($request->correct_answer, true);
        $correctAnswer = json_decode($question->correct_answers ?? $question->correct_answer, true);

        // 3️⃣ Check answer correctness
        if ($userAnswer[0] !== $correctAnswer[0]) {
            return response()->json([
                'status' => 'failed',
                'message' => "The answer '{$userAnswer[0]}' is incorrect",
                'explanation' => $question->explanation,
            ]);
        }

        // 4️⃣ Fetch user progress for this lesson
        $userProgress = UserProgressModel::firstOrCreate(
            ['user_id' => Auth::id(), 'lesson_id' => $question->lesson_id],
            ['points' => 0, 'is_completed' => false, 'time_spent' => 0, 'started_at' => now()]
        );

        // 5️⃣ Handle answered question
        $answeredQuestion = AnsweredQuestionsModel::firstOrNew(
            ['user_id' => Auth::id(), 'questions_id' => $questionId]
        );

        if ($answeredQuestion->is_passed) {
            return response()->json([
                'status' => 'success',
                'message' => 'You have already solved this question!',
                'answered_question' => $answeredQuestion,
                'question' => $question,
            ]);
        }

        // 6️⃣ Award points and mark question as passed
        $answeredQuestion->earned_points += 10;
        $answeredQuestion->is_passed = true;
        $answeredQuestion->save();

        $userProgress->points += 10;
        $userProgress->time_spent += $request->time_spent ?? 0;

        // 7️⃣ Determine next step with proper completion logic
        $currentLesson = $question->lesson;
        $currentUnit = $currentLesson->unit;
        $currentSection = $currentUnit->section;
        $currentCourse = $currentSection->course;

        $nextStep = null;
        $nextType = null;

        // Check if this question is the last one in the current lesson
        $isLastQuestionInLesson = $question->is_last;

        if (!$isLastQuestionInLesson) {
            // Next question in the same lesson
            $nextStep = QuestionsModel::where('lesson_id', $currentLesson->lesson_id)
                ->where('order', $question->order + 1)
                ->first();
            $nextType = 'question';
        } else {
            // Mark current lesson as completed
            $userProgress->is_completed = true;
            $userProgress->completed_at = now();
            $userProgress->save();

            // Check if there are more lessons in the current unit
            $nextLesson = LessonsModel::where('unit_id', $currentUnit->unit_id)
                ->where('order', $currentLesson->order + 1)
                ->first();

            if ($nextLesson && !$currentLesson->is_last) {
                // Next lesson in the same unit
                $nextStep = $nextLesson;
                $nextType = 'lesson';

                // Create progress record for next lesson
                UserProgressModel::firstOrCreate([
                    'user_id' => Auth::id(),
                    'lesson_id' => $nextLesson->lesson_id
                ], [
                    'points' => 0,
                    'is_completed' => false,
                    'time_spent' => 0,
                    'started_at' => now()
                ]);
            } else {
                // Current unit is completed, check for next unit
                $nextUnit = $currentSection->units()
                    ->where('order', $currentUnit->order + 1)
                    ->first();

                if ($nextUnit && !$currentUnit->is_last) {
                    // Next unit in the same section - get first lesson
                    $nextStep = $nextUnit->lessons()
                        ->where('order', 1)
                        ->first();
                    $nextType = 'unit';

                    if ($nextStep) {
                        UserProgressModel::firstOrCreate([
                            'user_id' => Auth::id(),
                            'lesson_id' => $nextStep->lesson_id
                        ], [
                            'points' => 0,
                            'is_completed' => false,
                            'time_spent' => 0,
                            'started_at' => now()
                        ]);
                    }
                } else {
                    // Current section is completed, check for next section
                    $nextSection = SectionsModel::where('course_id', $currentCourse->course_id)
                        ->where('order', $currentSection->order + 1)
                        ->first();

                    if ($nextSection && !$currentSection->is_last) {
                        // Next section - get first unit's first lesson
                        $firstUnitNextSection = $nextSection->units()
                            ->where('order', 1)
                            ->first();

                        if ($firstUnitNextSection) {
                            $nextStep = $firstUnitNextSection->lessons()
                                ->where('order', 1)
                                ->first();
                            $nextType = 'section';

                            if ($nextStep) {
                                UserProgressModel::firstOrCreate([
                                    'user_id' => Auth::id(),
                                    'lesson_id' => $nextStep->lesson_id
                                ], [
                                    'points' => 0,
                                    'is_completed' => false,
                                    'time_spent' => 0,
                                    'started_at' => now()
                                ]);
                            }
                        }
                    } else {
                        // Course completed!
                        $nextType = 'course_completed';
                        $nextStep = null;
                    }
                }
            }
        }

        // Save user progress changes
        $userProgress->save();

        // 8️⃣ Return final response
        return response()->json([
            'status' => 'success',
            'message' => 'Correct! +10 points',
            'next_type' => $nextType,
            'next_step' => $nextStep,
            'user_progress' => $userProgress,
            'current_level' => [
                'course' => $currentCourse->title,
                'section' => $currentSection->title,
                'unit' => $currentUnit->title,
                'lesson' => $currentLesson->title,
            ]
        ]);
    }
}
