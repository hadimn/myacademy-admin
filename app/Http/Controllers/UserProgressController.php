<?php

namespace App\Http\Controllers;

use App\Models\AnsweredQuestionsModel;
use App\Models\LessonsModel;
use App\Models\QuestionsModel;
use App\Models\SectionsModel;
use App\Models\UserBadgesModel;
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
        try {
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

            // 4️⃣ Get the lesson context from the question itself
            $currentLesson = $question->lesson;
            if (!$currentLesson) {
                return response()->json(['status' => 'failed', 'message' => 'Lesson not found for this question']);
            }

            $currentUnit = $currentLesson->unit;
            $currentSection = $currentUnit->section;
            $currentCourse = $currentSection->course;

            // 5️⃣ CHECK IF USER CAN ANSWER THIS QUESTION (ORDER VALIDATION - FIXED)
            // Get all questions in this lesson ordered by their order
            $allQuestionsInLesson = QuestionsModel::where('lesson_id', $currentLesson->lesson_id)
                ->orderBy('order')
                ->get();

            // Find the first unanswered question by checking each question in order
            $firstUnansweredQuestion = null;
            foreach ($allQuestionsInLesson as $q) {
                $isAnswered = AnsweredQuestionsModel::where('questions_id', $q->questions_id)
                    ->where('user_id', Auth::id())
                    ->where('is_passed', true)
                    ->exists();

                if (!$isAnswered) {
                    $firstUnansweredQuestion = $q;
                    break;
                }
            }

            // If there's an unanswered question that's NOT the current question, block access
            if ($firstUnansweredQuestion && $firstUnansweredQuestion->questions_id != $questionId) {
                return response()->json([
                    'status' => 'failed',
                    'message' => "Please complete question #{$firstUnansweredQuestion->order} first in this lesson",
                    'data' => [
                        'lesson_context' => [
                            'course' => $currentCourse->title,
                            'section' => $currentSection->title,
                            'unit' => $currentUnit->title,
                            'lesson' => $currentLesson->title,
                        ],
                        'required_question' => [
                            'questions_id' => $firstUnansweredQuestion->questions_id,
                            'order' => $firstUnansweredQuestion->order,
                            'title' => $firstUnansweredQuestion->title,
                        ],
                        'required_action' => 'complete_previous_question'
                    ]
                ]);
            }

            // 6️⃣ Fetch/Create user progress for THIS specific lesson (from the question)
            $userProgress = UserProgressModel::firstOrCreate(
                ['user_id' => Auth::id(), 'lesson_id' => $currentLesson->lesson_id],
                [
                    'points' => 0,
                    'is_completed' => false,
                    'time_spent' => 0,
                    'started_at' => now()
                ]
            );

            // 7️⃣ Handle answered question
            $answeredQuestion = AnsweredQuestionsModel::firstOrNew(
                ['user_id' => Auth::id(), 'questions_id' => $questionId]
            );

            if ($answeredQuestion->is_passed) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'You have already solved this question!',
                    'data' => [
                        'answered_question' => $answeredQuestion,
                        'question' => $question,
                        'lesson_context' => [
                            'course' => $currentCourse->title,
                            'section' => $currentSection->title,
                            'unit' => $currentUnit->title,
                            'lesson' => $currentLesson->title,
                        ],
                    ]
                ]);
            }

            // 8️⃣ Award points and mark question as passed
            $answeredQuestion->earned_points += 10;
            $answeredQuestion->is_passed = true;
            $answeredQuestion->save();

            $userProgress->points += 10;
            $userProgress->time_spent += $request->time_spent ?? 0;

            // 9️⃣ Determine next step within THIS lesson's context
            $nextStep = null;
            $nextType = null;
            $lessonCompleted = false;
            $courseCompleted = false;

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
                $lessonCompleted = true;

                // Check if there are more lessons in the current unit
                $nextLesson = LessonsModel::where('unit_id', $currentUnit->unit_id)
                    ->where('order', $currentLesson->order + 1)
                    ->first();

                if ($nextLesson && !$currentLesson->is_last) {
                    // Next lesson in the same unit
                    $nextStep = $nextLesson;
                    $nextType = 'lesson';

                    // Create progress record for next lesson (user can access it now)
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
                            $courseCompleted = true;
                        }
                    }
                }
            }

            // Save user progress changes - THIS WILL TRIGGER MODEL EVENTS
            $userProgress->save();

            // 🔟 Prepare base response
            $response = [
                'status' => 'success',
                'message' => 'Correct! +10 points',
                'data' => [
                    'lesson_context' => [
                        'course' => $currentCourse->title,
                        'section' => $currentSection->title,
                        'unit' => $currentUnit->title,
                        'lesson' => $currentLesson->title,
                    ],
                    'progress_recorded_in' => $currentLesson->title,
                    'next_type' => $nextType,
                    'next_step' => $nextStep,
                    'user_progress' => $userProgress,
                    'lesson_completed' => $lessonCompleted,
                ]
            ];

            // 🎯 CHECK FOR RECENTLY EARNED BADGES VIA DATABASE QUERY
            // Model events in UserProgressModel will handle badge awarding automatically
            $recentBadges = UserBadgesModel::where('user_id', Auth::id())
                ->where('earned_at', '>=', now()->subSeconds(10)) // Badges earned in last 10 seconds
                ->with('badge')
                ->get()
                ->pluck('badge')
                ->filter() // Remove null values
                ->values() // Reindex array
                ->toArray();

            if (!empty($recentBadges)) {
                $response['data']['badges_earned'] = $recentBadges;
                $response['data']['badges_count'] = count($recentBadges);

                // Add celebration message based on badge count
                if (count($recentBadges) === 1) {
                    $badgeName = is_array($recentBadges[0]) ? $recentBadges[0]['name'] : $recentBadges[0]->name;
                    $response['data']['celebration_message'] = "🎉 Amazing! You earned the '{$badgeName}' badge!";
                } else {
                    $badgeNames = collect($recentBadges)->map(function ($badge) {
                        return is_array($badge) ? $badge['name'] : $badge->name;
                    })->implode("', '");
                    $response['data']['celebration_message'] = "🎉 Incredible! You earned " . count($recentBadges) . " new badges: '{$badgeNames}'!";
                }

                // Update main message to include badge info
                $response['message'] = 'Correct! +10 points 🏆';
            }

            // 🎯 Add completion messages (these will be included alongside badge info)
            if ($lessonCompleted) {
                $response['data']['completion_message'] = "✅ Lesson '{$currentLesson->title}' completed!";

                // If course was completed, add special message
                if ($courseCompleted) {
                    $response['data']['course_completed'] = true;
                    $response['data']['course_completion_message'] = "🎓 Congratulations! You completed the course '{$currentCourse->title}'!";

                    // Update main message for course completion
                    $response['message'] = 'Course Completed! 🎓';

                    // Also check for course completion badges specifically
                    $courseCompletionBadges = UserBadgesModel::where('user_id', Auth::id())
                        ->where('earned_at', '>=', now()->subSeconds(10))
                        ->whereHas('badge', function ($query) {
                            $query->where('type', 'course_completion');
                        })
                        ->with('badge')
                        ->get()
                        ->pluck('badge')
                        ->toArray();

                    if (!empty($courseCompletionBadges)) {
                        // If we found course completion badges, merge them with existing badges
                        if (isset($response['data']['badges_earned'])) {
                            $response['data']['badges_earned'] = array_merge($response['data']['badges_earned'], $courseCompletionBadges);
                            $response['data']['badges_count'] = count($response['data']['badges_earned']);
                        } else {
                            $response['data']['badges_earned'] = $courseCompletionBadges;
                            $response['data']['badges_count'] = count($courseCompletionBadges);
                        }
                    }
                }
            }

            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                "status" => "failed",
                "message" => "Failed due to invalid inputs",
                "error" => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "failed",
                "message" => "Failed due to an error",
                "error" => $e->getMessage(),
            ]);
        }
    }
}
