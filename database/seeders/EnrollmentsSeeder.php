<?php

namespace Database\Seeders;

use App\Models\EnrollmentsModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EnrollmentsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Get all users
        $userIds = User::pluck('id')->all();

        // Get all courses with pricing
        $coursePricings = DB::table('course_pricings')
            ->join('courses', 'course_pricings.course_id', '=', 'courses.course_id')
            ->select(
                'courses.course_id',
                'course_pricings.price',
                'course_pricings.is_free',
                'course_pricings.discount_price'
            )
            ->get();

        if (empty($userIds) || $coursePricings->isEmpty()) {
            echo "Skipping EnrollmentsSeeder: Users or Course Pricings not found.\n";
            return;
        }

        $paymentMethods = ['Stripe', 'PayPal', 'Credit Card'];
        $enrollmentsCreated = 0;

        foreach ($userIds as $userId) {
            foreach ($coursePricings as $course) {
                // Price calculation
                $finalPrice = $course->discount_price > 0
                    ? $course->discount_price
                    : $course->price;

                $isFree = (bool) $course->is_free;

                $transactionId = null;
                $paymentStatus = 'paid';
                $paymentMethod = $isFree ? 'Free Enrollment' : $faker->randomElement($paymentMethods);
                $amountPaid = $isFree ? 0.00 : $finalPrice;

                if (!$isFree) {
                    $statusRoll = $faker->numberBetween(1, 100);

                    if ($statusRoll <= 80) {
                        // Paid successfully
                        $paymentStatus = 'paid';
                        $transactionId = 'txn_' . $faker->unique()->sha1();
                    } elseif ($statusRoll <= 95) {
                        // Failed
                        $paymentStatus = 'failed';
                        $transactionId = 'txn_fail_' . $faker->unique()->sha1();
                        $amountPaid = 0.00;
                    } else {
                        // Refunded
                        $paymentStatus = 'refunded';
                        $transactionId = 'txn_ref_' . $faker->unique()->sha1();
                    }
                }

                // Enrollment date (past 6 months)
                $enrolledAt = Carbon::instance(
                    $faker->dateTimeBetween('-6 months', 'now')
                );

                // Random completion - 45% chance of completion, but only for paid enrollments
                $completedAt = null;
                if ($paymentStatus === 'paid' && $faker->boolean(45)) {
                    // Make sure completion date is after enrollment date
                    $minCompletionDate = $enrolledAt->copy()->addDays(1);
                    
                    // Ensure minCompletionDate is not in the future
                    $endDate = Carbon::now();
                    if ($minCompletionDate->gt($endDate)) {
                        // If min completion date is in the future, don't set completion
                        $completedAt = null;
                    } else {
                        $completedAt = $faker->dateTimeBetween(
                            $minCompletionDate,
                            $endDate
                        );
                    }
                }

                EnrollmentsModel::create([
                    'user_id'        => $userId,
                    'course_id'      => $course->course_id,
                    'amount_paid'    => $amountPaid,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $paymentMethod,
                    'transaction_id' => $transactionId,
                    'enrolled_at'    => $enrolledAt,
                    'completed_at'   => $completedAt,
                    'created_at'     => $enrolledAt,
                    'updated_at'     => $faker->dateTimeBetween($enrolledAt, 'now'),
                ]);

                $enrollmentsCreated++;
            }
        }

        echo "Successfully created {$enrollmentsCreated} enrollment records.\n";
        echo "Each of " . count($userIds) . " users enrolled in " . count($coursePricings) . " courses.\n";
    }
}