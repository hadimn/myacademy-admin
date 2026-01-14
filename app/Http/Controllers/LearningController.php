<?php

namespace App\Http\Controllers;

use App\Models\AnsweredQuestionsModel;
use App\Models\CoursesModel;
use App\Models\EnrollmentsModel;
use App\Models\LessonsModel;
use App\Models\UnitsModel;
use App\Models\SectionsModel;
use App\Models\UserProgressModel;
use App\Services\BadgeService;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LearningController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get enrolled courses for the authenticated user
     */
    public function getEnrolledCourses()
    {
        try {
            $user = Auth::user();

            $enrolledCourses = EnrollmentsModel::where('user_id', $user->id)
                ->with('course')
                ->get()
                ->map(function ($enrollment) {
                    $course = $enrollment->course;
                    $totalLessons = $this->getTotalLessonsInCourse($course->course_id);
                    $completedLessons = $this->getCompletedLessonsInCourse($enrollment->user_id, $course->course_id);

                    return [
                        'enrollment_id' => $enrollment->enrollment_id,
                        'course_id' => $course->course_id,
                        'title' => $course->title,
                        'description' => $course->description,
                        'image_url' => $course->image_url ? asset('/storage/' . $course->image_url) : null,
                        'level' => $course->level,
                        'language' => $course->language,
                        'total_lessons' => $totalLessons,
                        'completed_lessons' => $completedLessons,
                        'progress_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0,
                        'enrolled_at' => $enrollment->enrolled_at,
                        'completed_at' => $enrollment->completed_at,
                        'is_completed' => $enrollment->completed_at !== null,
                    ];
                });

            return $this->successResponse(
                $enrolledCourses,
                "Enrolled courses retrieved successfully!",
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving enrolled courses:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                "Failed to retrieve enrolled courses",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get course structure (sections, units, lessons) for learning
     */
    public function getCourseStructure($courseId)
    {
        try {
            $user = Auth::user();

            $course = CoursesModel::with(['sections' => function ($query) {
                $query->orderBy('order');
            }, 'sections.units' => function ($query) {
                $query->orderBy('order');
            }, 'sections.units.lessons' => function ($query) {
                $query->orderBy('order');
            }])->findOrFail($courseId);

            // Check if user is enrolled
            $enrollment = EnrollmentsModel::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            if (!$enrollment) {
                return $this->errorResponse(
                    "You are not enrolled in this course",
                    Response::HTTP_FORBIDDEN
                );
            }

            $structure = [
                'course_id' => $course->course_id,
                'title' => $course->title,
                'description' => $course->description,
                'level' => $course->level,
                'is_completed' => $enrollment->completed_at !== null,
                'sections' => $course->sections->map(function ($section) use ($user) {
                    return [
                        'section_id' => $section->section_id,
                        'title' => $section->title,
                        'description' => $section->description,
                        'order' => $section->order,
                        'is_last' => $section->is_last,
                        'units' => $section->units->map(function ($unit) use ($user) {
                            return [
                                'unit_id' => $unit->unit_id,
                                'title' => $unit->title,
                                'color' => $unit->color,
                                'order' => $unit->order,
                                'is_last' => $unit->is_last,
                                'lessons' => $unit->lessons->map(function ($lesson) use ($user) {
                                    $progress = UserProgressModel::where('user_id', $user->id)
                                        ->where('lesson_id', $lesson->lesson_id)
                                        ->first();

                                    return [
                                        'lesson_id' => $lesson->lesson_id,
                                        'title' => $lesson->title,
                                        'description' => $lesson->description,
                                        'order' => $lesson->order,
                                        'is_completed' => $progress?->is_completed ?? false,
                                        'is_last' => $lesson->is_last,
                                        'chest_after' => $lesson->chest_after,
                                    ];
                                }),
                            ];
                        }),
                    ];
                }),
            ];

            return $this->successResponse(
                $structure,
                "Course structure retrieved successfully!",
                Response::HTTP_OK
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                "Course not found",
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving course structure:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                "Failed to retrieve course structure",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get lesson with questions for learning
     */
    public function getLesson($lessonId)
    {
        try {
            $user = Auth::user();

            $lesson = LessonsModel::with([
                'questions' => function ($query) {
                    $query->orderBy('order');
                },
                'unit.section.course'
            ])->findOrFail($lessonId);

            // Verify user is enrolled in the course
            $courseId = $lesson->unit->section->course->course_id;

            $enrollment = EnrollmentsModel::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->first();

            if (!$enrollment) {
                return $this->errorResponse(
                    "You are not enrolled in this course",
                    Response::HTTP_FORBIDDEN
                );
            }

            // Get user progress for this lesson
            $progress = UserProgressModel::where('user_id', $user->id)
                ->where('lesson_id', $lessonId)
                ->first();

            // Get navigation info
            $navigation = $this->getNavigationInfo($lesson);

            $lessonData = [
                'lesson_id' => $lesson->lesson_id,
                'title' => $lesson->title,
                'description' => $lesson->description,
                'content' => $lesson->content,
                'video_url' => $lesson->video_url,
                'image_url' => $lesson->image_url ? asset('/storage/' . $lesson->image_url) : null,
                'duration' => $lesson->duration,
                'lesson_type' => $lesson->lesson_type,
                'is_last' => $lesson->is_last,
                'chest_after' => $lesson->chest_after,
                'order' => $lesson->order,
                'course_id' => $courseId,
                'unit_id' => $lesson->unit_id,
                'section_id' => $lesson->unit->section->section_id,
                'is_completed' => $progress?->is_completed ?? false,
                'navigation' => $navigation,
                'questions' => $lesson->questions->map(function ($question) {
                    return [
                        'questions_id' => $question->questions_id,
                        'lesson_id' => $question->lesson_id,
                        'title' => $question->title,
                        'description' => $question->description,
                        'video_url' => $question->video_url ? asset('/storage/' . $question->video_url) : null,
                        'image_url' => $question->image_url ? asset('/storage/' . $question->image_url) : null,
                        'points' => $question->points,
                        'is_last' => $question->is_last,
                        'question_type' => $question->question_type,
                        'options' => $question->options ?? [],
                        'correct_answer' => $question->correct_answer ?? [],
                        'order' => $question->order,
                        'explanation' => $question->explanation ?? null,
                    ];
                }),
            ];

            return $this->successResponse(
                $lessonData,
                "Lesson retrieved successfully!",
                Response::HTTP_OK
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                "Lesson not found",
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            Log::error('Error retrieving lesson:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                "Failed to retrieve lesson",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Submit lesson answers and update progress
     */
    public function submitLessonAnswers(Request $request, $lessonId, BadgeService $badgeService)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'answers' => 'required|array',
                'time_spent' => 'nullable|integer|min:0',
            ]);

            DB::beginTransaction();

            $lesson = LessonsModel::with('questions', 'unit.section.course')->findOrFail($lessonId);

            // Verify enrollment
            $courseId = $lesson->unit->section->course->course_id;
            $enrollment = EnrollmentsModel::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->firstOrFail();

            // Calculate score
            $totalQuestions = $lesson->questions->count();
            $correctAnswers = 0;
            $totalPoints = 0;

            foreach ($request->answers as $questionId => $userAnswer) {
                $question = $lesson->questions->firstWhere('questions_id', $questionId);

                if (!$question) {
                    continue;
                }

                $correctAnswerArray = $question->correct_answer;
                $isCorrect = $this->checkAnswer($userAnswer, $correctAnswerArray, $question->question_type);

                if ($isCorrect) {
                    $correctAnswers++;
                    $totalPoints += $question->points;
                }

                // Record individual answered question
                AnsweredQuestionsModel::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'questions_id' => $questionId,
                    ],
                    [
                        'earned_points' => $isCorrect ? $question->points : 0,
                        'is_passed' => $isCorrect,
                    ]
                );
            }

            $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0;
            $isPassed = $score >= 70;

            // Update or create user progress
            $progress = UserProgressModel::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'lesson_id' => $lessonId,
                ],
                [
                    'course_id' => $courseId,
                    'section_id' => $lesson->unit->section->section_id,
                    'unit_id' => $lesson->unit_id,
                    'is_completed' => $isPassed,
                    'time_spent' => $request->time_spent ?? 0,
                    'points' => $isPassed ? $totalPoints : 0,
                    'started_at' => UserProgressModel::where('user_id', $user->id)
                        ->where('lesson_id', $lessonId)
                        ->first()?->started_at ?? now(),
                    'completed_at' => $isPassed ? now() : null,
                ]
            );

            $newlyAwardedBadges = [];
            $courseCompleted = false;
            $nextLesson = null;

            if ($isPassed) {
                // Check if course is completed
                // Always check if course is completed (for tracking)
                $courseCompleted = $this->checkAndCompleteCourse($user->id, $courseId, $enrollment);

                // ALWAYS get next lesson, even if course is completed (for review/redo)
                $nextLesson = $this->findNextLessonWithOrder($lesson);

                // Award badges
                $awardedBadges = $badgeService->checkAndAwardBadges($user);

                // Get only badges earned in the last 2 minutes
                $recentTime = Carbon::now()->subMinutes(2);

                $newlyAwardedBadges = $user->badges()
                    ->wherePivot('earned_at', '>=', $recentTime)
                    ->get()
                    ->map(function ($badge) {
                        return [
                            'badge_id' => $badge->badge_id,
                            'name' => $badge->name,
                            'description' => $badge->description,
                            'icon' => $badge->icon,
                            'type' => $badge->type,
                            'criteria' => $badge->criteria,
                            'points' => $badge->points,
                        ];
                    })
                    ->toArray();
            }

            DB::commit();

            $message = $isPassed ? 'Great job! You passed the lesson!' : 'You need 70% to pass. Try again!';

            if ($courseCompleted) {
                $message = 'Congratulations! You have completed the entire course!';
            }

            return $this->successResponse(
                [
                    'is_passed' => $isPassed,
                    'score' => $score,
                    'correct_answers' => $correctAnswers,
                    'total_questions' => $totalQuestions,
                    'points_earned' => $isPassed ? $totalPoints : 0,
                    'course_completed' => $courseCompleted,
                    'next_lesson' => $nextLesson ? [
                        'lesson_id' => $nextLesson->lesson_id,
                        'title' => $nextLesson->title,
                        'order' => $nextLesson->order,
                    ] : null,
                    'message' => $message,
                    'newly_awarded_badges' => $newlyAwardedBadges,
                ],
                "Lesson answered successfully!",
                Response::HTTP_OK
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->errorResponse(
                "Lesson or course not found",
                Response::HTTP_NOT_FOUND
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse(
                "Validation failed",
                Response::HTTP_UNPROCESSABLE_ENTITY,
                [$e->getMessage()]
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting lesson answers:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                "Failed to submit lesson answers",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get next lesson in course progression with proper order checking
     */
    public function getNextLesson($currentLessonId)
    {
        try {
            $user = Auth::user();

            $currentLesson = LessonsModel::with('unit.section.course')->findOrFail($currentLessonId);
            $courseId = $currentLesson->unit->section->course->course_id;

            // Verify enrollment
            $enrollment = EnrollmentsModel::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->firstOrFail();

            // Check if course is already completed
            $isCourseCompleted = $enrollment->completed_at !== null;

            // Find next lesson using order-based hierarchy
            $nextLesson = $this->findNextLessonWithOrder($currentLesson);

            if (!$nextLesson) {
                // This means we've reached the end of the course
                $isCourseCompleted = $enrollment->completed_at !== null;

                return $this->successResponse(
                    [
                        'next_lesson' => null,
                        'is_course_completed' => $isCourseCompleted,
                    ],
                    $isCourseCompleted
                        ? "You have reached the end of this course!"
                        : "No next lesson found in the course progression.",
                    Response::HTTP_OK
                );
            }

            return $this->successResponse(
                [
                    'lesson_id' => $nextLesson->lesson_id,
                    'title' => $nextLesson->title,
                    'order' => $nextLesson->order,
                    'unit_id' => $nextLesson->unit_id,
                    'section_id' => $nextLesson->unit->section_id,
                    'is_course_completed' => $isCourseCompleted,
                ],
                "Next lesson retrieved successfully!",
                Response::HTTP_OK
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                "Lesson or enrollment not found",
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            Log::error('Error getting next lesson:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                "Failed to get next lesson",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get course progress statistics
     */
    public function getCourseProgress($courseId)
    {
        try {
            $user = Auth::user();

            // Verify enrollment
            $enrollment = EnrollmentsModel::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->firstOrFail();

            $totalLessons = $this->getTotalLessonsInCourse($courseId);
            $completedLessons = $this->getCompletedLessonsInCourse($user->id, $courseId);
            $totalPoints = UserProgressModel::where('user_id', $user->id)
                ->where('course_id', $courseId)
                ->where('is_completed', true)
                ->sum('points');

            $progressPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

            // Get current lesson (next incomplete lesson)
            $currentLesson = $this->getCurrentLesson($user->id, $courseId);

            return $this->successResponse(
                [
                    'course_id' => $courseId,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'progress_percentage' => $progressPercentage,
                    'total_points' => $totalPoints,
                    'is_completed' => $enrollment->completed_at !== null,
                    'enrolled_at' => $enrollment->enrolled_at,
                    'completed_at' => $enrollment->completed_at,
                    'current_lesson' => $currentLesson ? [
                        'lesson_id' => $currentLesson->lesson_id,
                        'title' => $currentLesson->title,
                        'unit_id' => $currentLesson->unit_id,
                        'section_id' => $currentLesson->unit->section_id,
                    ] : null,
                ],
                "Course progress retrieved successfully!",
                Response::HTTP_OK
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse(
                "Course enrollment not found",
                Response::HTTP_NOT_FOUND
            );
        } catch (\Exception $e) {
            Log::error('Error getting course progress:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse(
                "Failed to get course progress",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Find next lesson with proper order-based hierarchy
     * Checks: lesson order → unit order → section order
     */
    private function findNextLessonWithOrder(LessonsModel $currentLesson): ?LessonsModel
    {
        $currentUnit = $currentLesson->unit;
        $currentSection = $currentUnit->section;

        Log::info('Finding next lesson with order checking:', [
            'current_lesson_id' => $currentLesson->lesson_id,
            'current_order' => $currentLesson->order,
            'lesson_is_last' => $currentLesson->is_last,
            'unit_id' => $currentUnit->unit_id,
            'unit_order' => $currentUnit->order,
            'unit_is_last' => $currentUnit->is_last,
            'section_id' => $currentSection->section_id,
            'section_order' => $currentSection->order,
            'section_is_last' => $currentSection->is_last,
        ]);

        // 1. Try to find next lesson in the same unit (by order)
        $nextLesson = LessonsModel::where('unit_id', $currentUnit->unit_id)
            ->where('order', '>', $currentLesson->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($nextLesson) {
            Log::info('Found next lesson in same unit', ['next_lesson_id' => $nextLesson->lesson_id]);
            return $nextLesson;
        }

        // 2. Check if current lesson is marked as last in unit OR if it's actually the last by order
        $isLastLessonInUnit = $currentLesson->is_last || $this->isLastLessonByOrderInUnit($currentLesson);

        if ($isLastLessonInUnit) {
            Log::info('Current lesson is last in unit, checking next unit');

            // 3. Try to find next unit in the same section (by order)
            $nextUnit = UnitsModel::where('section_id', $currentSection->section_id)
                ->where('order', '>', $currentUnit->order)
                ->orderBy('order', 'asc')
                ->first();

            if ($nextUnit) {
                // Get first lesson of next unit (by order)
                $nextLesson = LessonsModel::where('unit_id', $nextUnit->unit_id)
                    ->orderBy('order', 'asc')
                    ->first();

                if ($nextLesson) {
                    Log::info('Found first lesson in next unit', ['next_lesson_id' => $nextLesson->lesson_id]);
                    return $nextLesson;
                }
            } else {
                // 4. Check if current unit is marked as last in section OR if it's actually the last by order
                $isLastUnitInSection = $currentUnit->is_last || $this->isLastUnitByOrderInSection($currentUnit);

                if ($isLastUnitInSection) {
                    Log::info('Current unit is last in section, checking next section');

                    // 5. Try to find next section in the course (by order)
                    $nextSection = SectionsModel::where('course_id', $currentSection->course_id)
                        ->where('order', '>', $currentSection->order)
                        ->orderBy('order', 'asc')
                        ->first();

                    if ($nextSection) {
                        // Get first unit of next section (by order)
                        $firstUnit = UnitsModel::where('section_id', $nextSection->section_id)
                            ->orderBy('order', 'asc')
                            ->first();

                        if ($firstUnit) {
                            // Get first lesson of first unit in next section (by order)
                            $nextLesson = LessonsModel::where('unit_id', $firstUnit->unit_id)
                                ->orderBy('order', 'asc')
                                ->first();

                            if ($nextLesson) {
                                Log::info('Found first lesson in next section', ['next_lesson_id' => $nextLesson->lesson_id]);
                                return $nextLesson;
                            }
                        }
                    } else {
                        // 6. Check if current section is marked as last in course OR if it's actually the last by order
                        $isLastSectionInCourse = $currentSection->is_last || $this->isLastSectionByOrderInCourse($currentSection);

                        if ($isLastSectionInCourse) {
                            Log::info('Current section is last in course - course may be completed');
                            return null; // Course is completed
                        }
                    }
                }
            }
        }

        Log::info('No next lesson found');
        return null;
    }

    /**
     * Check if a lesson is the last in its unit by order
     */
    private function isLastLessonByOrderInUnit(LessonsModel $lesson): bool
    {
        $lastLesson = LessonsModel::where('unit_id', $lesson->unit_id)
            ->orderBy('order', 'desc')
            ->first();

        return $lastLesson && $lastLesson->lesson_id === $lesson->lesson_id;
    }

    /**
     * Check if a unit is the last in its section by order
     */
    private function isLastUnitByOrderInSection(UnitsModel $unit): bool
    {
        $lastUnit = UnitsModel::where('section_id', $unit->section_id)
            ->orderBy('order', 'desc')
            ->first();

        return $lastUnit && $lastUnit->unit_id === $unit->unit_id;
    }

    /**
     * Check if a section is the last in its course by order
     */
    private function isLastSectionByOrderInCourse(SectionsModel $section): bool
    {
        $lastSection = SectionsModel::where('course_id', $section->course_id)
            ->orderBy('order', 'desc')
            ->first();

        return $lastSection && $lastSection->section_id === $section->section_id;
    }

    /**
     * Get navigation information for a lesson
     */
    private function getNavigationInfo(LessonsModel $lesson): array
    {
        $unit = $lesson->unit;
        $section = $unit->section;

        // Get previous lesson if exists
        $previousLesson = LessonsModel::where('unit_id', $unit->unit_id)
            ->where('order', '<', $lesson->order)
            ->orderBy('order', 'desc')
            ->first();

        // Get next lesson using order-based hierarchy
        $nextLesson = $this->findNextLessonWithOrder($lesson);

        return [
            'current_position' => [
                'section_title' => $section->title,
                'section_order' => $section->order,
                'section_is_last' => $section->is_last,
                'unit_title' => $unit->title,
                'unit_order' => $unit->order,
                'unit_is_last' => $unit->is_last,
                'unit_color' => $unit->color,
                'lesson_order' => $lesson->order,
                'lesson_is_last' => $lesson->is_last,
            ],
            'previous_lesson' => $previousLesson ? [
                'lesson_id' => $previousLesson->lesson_id,
                'title' => $previousLesson->title,
            ] : null,
            'next_lesson' => $nextLesson ? [
                'lesson_id' => $nextLesson->lesson_id,
                'title' => $nextLesson->title,
            ] : null,
            'hierarchy_info' => [
                'is_last_lesson_in_unit' => $lesson->is_last || $this->isLastLessonByOrderInUnit($lesson),
                'is_last_unit_in_section' => $unit->is_last || $this->isLastUnitByOrderInSection($unit),
                'is_last_section_in_course' => $section->is_last || $this->isLastSectionByOrderInCourse($section),
            ],
        ];
    }

    /**
     * Get current lesson (next incomplete lesson) for a user in a course
     */
    /**
     * Get current lesson (next incomplete lesson) for a user in a course
     */
    private function getCurrentLesson(int $userId, int $courseId): ?LessonsModel
    {
        // Get all lessons in the course ordered by hierarchy
        $allLessons = LessonsModel::whereHas('unit.section', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })
            ->with(['unit' => function ($query) {
                $query->with(['section' => function ($query) {
                    $query->orderBy('order', 'asc');
                }])->orderBy('order', 'asc');
            }])
            ->orderBy('order', 'asc')
            ->get();

        // Manually sort the lessons by section order, unit order, then lesson order
        $sortedLessons = $allLessons->sortBy(function ($lesson) {
            return [
                $lesson->unit->section->order ?? 0,
                $lesson->unit->order ?? 0,
                $lesson->order
            ];
        })->values();

        // Get completed lessons for this user
        $completedLessonIds = UserProgressModel::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('is_completed', true)
            ->pluck('lesson_id')
            ->toArray();

        // Find the first incomplete lesson
        foreach ($sortedLessons as $lesson) {
            if (!in_array($lesson->lesson_id, $completedLessonIds)) {
                return $lesson;
            }
        }

        // All lessons are completed
        return null;
    }

    /**
     * Helper: Check if an answer is correct
     */
    private function checkAnswer($userAnswer, array $correctAnswerArray, string $questionType): bool
    {
        if (empty($correctAnswerArray)) {
            return false;
        }

        foreach ($correctAnswerArray as $correctAnswer) {
            if ($questionType === 'fill') {
                if (strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer))) {
                    return true;
                }
            } else {
                if ((string)$userAnswer === (string)$correctAnswer) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Helper: Check if course is completed and update enrollment
     */
    private function checkAndCompleteCourse(int $userId, int $courseId, EnrollmentsModel $enrollment): bool
    {
        $totalLessons = $this->getTotalLessonsInCourse($courseId);
        $completedLessons = $this->getCompletedLessonsInCourse($userId, $courseId);

        if ($totalLessons > 0 && $totalLessons === $completedLessons) {
            $enrollment->update(['completed_at' => now()]);

            Log::info('Course completed', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'completed_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Helper: Get total lessons in a course
     */
    private function getTotalLessonsInCourse(int $courseId): int
    {
        return LessonsModel::whereHas('unit.section', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();
    }

    /**
     * Helper: Get completed lessons for a user in a course
     */
    private function getCompletedLessonsInCourse(int $userId, int $courseId): int
    {
        return UserProgressModel::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('is_completed', true)
            ->distinct('lesson_id')
            ->count('lesson_id');
    }
}
