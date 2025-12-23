<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\AnsweredQuestionsModel;
use App\Models\BadgesModel;
use App\Models\CoursePricingModel;
use App\Models\CoursesModel;
use App\Models\EnrollmentsModel;
use App\Models\LessonsModel;
use App\Models\QuestionsModel;
use App\Models\SectionsModel;
use App\Models\UnitsModel;
use App\Models\User;
use App\Models\UserBadgesModel;
use App\Models\UserProgressModel;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get basic counts for sidebar badges
     */
    public function counts()
    {
        try {
            return response()->json([
                'admins' => Admin::count(),
                'users' => User::count(),
                'badges' => BadgesModel::count(),
                'courses' => CoursesModel::count(),
                'sections' => SectionsModel::count(),
                'units' => UnitsModel::count(),
                'lessons' => LessonsModel::count(),
                'questions' => QuestionsModel::count(),
                'answered_questions' => AnsweredQuestionsModel::count(),
                'user_progress' => UserProgressModel::count(),
                'course_pricing' => CoursePricingModel::count(),
                'enrollments' => EnrollmentsModel::count(),
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch counts',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get comprehensive dashboard statistics
     */
    public function stats()
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_courses' => CoursesModel::count(),
                'total_enrollments' => EnrollmentsModel::count(),
                'total_revenue' => EnrollmentsModel::where('payment_status', 'paid')
                    ->sum('amount_paid'),
                'active_users_today' => User::whereDate('last_activity_date', Carbon::today())->count(),
                'completed_courses' => EnrollmentsModel::whereNotNull('completed_at')->count(),
                'pending_payments' => EnrollmentsModel::where('payment_status', 'pending')->count(),
                'total_badges_earned' => UserBadgesModel::count(),
            ];

            return $this->successResponse(
                $stats,
                'Dashboard statistics retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch statistics',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get revenue data for charts
     */
    public function revenue(Request $request)
    {
        try {
            $period = $request->input('period', 'month');
            
            $dateFormat = match ($period) {
                'day' => '%Y-%m-%d %H:00:00',
                'week' => '%Y-%m-%d',
                'month' => '%Y-%m-%d',
                'year' => '%Y-%m',
                default => '%Y-%m-%d',
            };

            $startDate = match ($period) {
                'day' => Carbon::today(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            $revenueData = EnrollmentsModel::where('payment_status', 'paid')
                ->where('enrolled_at', '>=', $startDate)
                ->select(
                    DB::raw("DATE_FORMAT(enrolled_at, '$dateFormat') as date"),
                    DB::raw('SUM(amount_paid) as amount'),
                    DB::raw('COUNT(*) as enrollments')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return $this->successResponse(
                $revenueData,
                'Revenue data retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch revenue data',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get top performing courses
     */
    public function topCourses(Request $request)
    {
        try {
            $period = $request->input('period', 'month');
            $limit = $request->input('limit', 10);

            $startDate = match ($period) {
                'day' => Carbon::today(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            $topCourses = CoursesModel::select(
                'courses.course_id',
                'courses.title',
                DB::raw('COUNT(enrollments.enrollment_id) as enrollments'),
                DB::raw('SUM(CASE WHEN enrollments.payment_status = "paid" THEN enrollments.amount_paid ELSE 0 END) as revenue'),
                DB::raw('COUNT(CASE WHEN enrollments.completed_at IS NOT NULL THEN 1 END) * 100.0 / COUNT(enrollments.enrollment_id) as completion_rate')
            )
                ->leftJoin('enrollments', 'courses.course_id', '=', 'enrollments.course_id')
                ->where('enrollments.enrolled_at', '>=', $startDate)
                ->groupBy('courses.course_id', 'courses.title')
                ->orderByDesc('revenue')
                ->limit($limit)
                ->get();

            return $this->successResponse(
                $topCourses,
                'Top courses retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch top courses',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get recent enrollments
     */
    public function recentEnrollments(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);

            $recentEnrollments = EnrollmentsModel::with(['user', 'course'])
                ->select('enrollments.*')
                ->orderByDesc('enrolled_at')
                ->limit($limit)
                ->get()
                ->map(function ($enrollment) {
                    return [
                        'enrollment_id' => $enrollment->enrollment_id,
                        'user_name' => $enrollment->user->name ?? 'Unknown',
                        'course_title' => $enrollment->course->title ?? 'Unknown',
                        'amount_paid' => $enrollment->amount_paid,
                        'payment_status' => $enrollment->payment_status,
                        'enrolled_at' => $enrollment->enrolled_at,
                    ];
                });

            return $this->successResponse(
                $recentEnrollments,
                'Recent enrollments retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch recent enrollments',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get user growth data
     */
    public function userGrowth(Request $request)
    {
        try {
            $period = $request->input('period', 'month');

            $dateFormat = match ($period) {
                'day' => '%Y-%m-%d %H:00:00',
                'week' => '%Y-%m-%d',
                'month' => '%Y-%m-%d',
                'year' => '%Y-%m',
                default => '%Y-%m-%d',
            };

            $startDate = match ($period) {
                'day' => Carbon::today(),
                'week' => Carbon::now()->subWeek(),
                'month' => Carbon::now()->subMonth(),
                'year' => Carbon::now()->subYear(),
                default => Carbon::now()->subMonth(),
            };

            $userGrowth = User::where('created_at', '>=', $startDate)
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '$dateFormat') as date"),
                    DB::raw('COUNT(*) as new_users')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Calculate cumulative total
            $total = User::where('created_at', '<', $startDate)->count();
            $userGrowth = $userGrowth->map(function ($item) use (&$total) {
                $total += $item->new_users;
                $item->total_users = $total;
                return $item;
            });

            return $this->successResponse(
                $userGrowth,
                'User growth data retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch user growth data',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get payment status distribution
     */
    public function paymentStatusDistribution()
    {
        try {
            $distribution = EnrollmentsModel::select(
                'payment_status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount_paid) as total_amount')
            )
                ->groupBy('payment_status')
                ->get();

            return $this->successResponse(
                $distribution,
                'Payment status distribution retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch payment status distribution',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }

    /**
     * Get course completion stats
     */
    public function courseCompletionStats()
    {
        try {
            $stats = [
                'total_enrolled' => EnrollmentsModel::count(),
                'completed' => EnrollmentsModel::whereNotNull('completed_at')->count(),
                'in_progress' => EnrollmentsModel::whereNull('completed_at')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('user_progress')
                            ->whereColumn('user_progress.course_id', 'enrollments.course_id')
                            ->whereColumn('user_progress.user_id', 'enrollments.user_id');
                    })
                    ->count(),
                'not_started' => EnrollmentsModel::whereNull('completed_at')
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('user_progress')
                            ->whereColumn('user_progress.course_id', 'enrollments.course_id')
                            ->whereColumn('user_progress.user_id', 'enrollments.user_id');
                    })
                    ->count(),
            ];

            $stats['completion_rate'] = $stats['total_enrolled'] > 0
                ? round(($stats['completed'] / $stats['total_enrolled']) * 100, 2)
                : 0;

            return $this->successResponse(
                $stats,
                'Course completion stats retrieved successfully',
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to fetch course completion stats',
                Response::HTTP_INTERNAL_SERVER_ERROR,
                [$e->getMessage()]
            );
        }
    }
}