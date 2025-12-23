<?php

namespace Database\Seeders;

use App\Models\EnrollmentsModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EnrollmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Get IDs and Pricing Data
        $userIds = User::pluck('id')->all();
        // Get courses and their current pricing
        $coursePricings = DB::table('course_pricings')
                            ->join('courses', 'course_pricings.course_id', '=', 'courses.course_id')
                            ->select('courses.course_id', 'course_pricings.price', 'course_pricings.is_free', 'course_pricings.discount_price')
                            ->get()
                            ->keyBy('course_id');

        if (empty($userIds) || $coursePricings->isEmpty()) {
            echo "Skipping EnrollmentsSeeder: Users or Course Pricings not found. Please seed prerequisites.\n";
            return;
        }

        $enrollmentsCreated = 0;
        $paymentMethods = ['Stripe', 'PayPal', 'Credit Card'];
        $paymentStatuses = ['paid', 'failed', 'refunded'];


        foreach ($userIds as $userId) {
            // Each user enrolls in a random number of courses (1 to 5)
            $numCoursesToEnroll = $faker->numberBetween(1, 5);
            
            // Randomly select unique courses for the user
            $courseIdsToEnroll = $faker->randomElements($coursePricings->keys()->all(), $numCoursesToEnroll, false);
            
            foreach ($courseIdsToEnroll as $courseId) {
                $pricing = $coursePricings->get($courseId);

                // Determine the price based on discount status (use base price if discount_price is 0.00)
                $finalPrice = $pricing->discount_price > 0.00 ? $pricing->discount_price : $pricing->price;
                $isFree = (bool)$pricing->is_free;


                $transactionId = null;
                $paymentStatus = 'paid';
                $paymentMethod = $isFree ? 'Free Enrollment' : $faker->randomElement($paymentMethods);
                $amountPaid = $finalPrice;

                if ($isFree) {
                    // Free course logic
                    $amountPaid = 0.00;
                    $paymentStatus = 'paid'; // Automatically 'paid'
                } else {
                    // Paid course logic: Simulate success, failure, or refund
                    $statusRoll = $faker->numberBetween(1, 100);

                    if ($statusRoll <= 80) { // 80% success
                        $paymentStatus = 'paid';
                        $transactionId = 'txn_' . $faker->unique()->sha1();
                    } elseif ($statusRoll <= 95) { // 15% failed
                        $paymentStatus = 'failed';
                        $transactionId = 'txn_fail_' . $faker->unique()->sha1();
                        $amountPaid = 0.00; // No payment actually processed
                    } else { // 5% refunded
                        $paymentStatus = 'refunded';
                        $transactionId = 'txn_ref_' . $faker->unique()->sha1();
                        // amountPaid remains finalPrice, representing money that was returned
                    }
                }
                
                // Set enrollment time, ensuring the course is not enrolled in the future
                $enrolledAt = Carbon::now(0);
                $completedAt = null;

                // Randomly set completion status for older, successfully paid/free courses
                if ($paymentStatus === 'paid' && $faker->boolean(40)) {
                    // Completion date is after enrollment date, but not in the future
                    $completedAt = $faker->dateTimeBetween($enrolledAt, 'now');
                }
                
                // Create the enrollment record
                EnrollmentsModel::create([
                    'user_id'           => $userId,
                    'course_id'         => $courseId,
                    'amount_paid'       => $amountPaid,
                    'payment_status'    => $paymentStatus,
                    'payment_method'    => $paymentMethod,
                    // Note: Transaction ID is set as a string hash to match the migration type
                    'transaction_id'    => $transactionId, 
                    'enrolled_at'       => $enrolledAt,
                    'completed_at'      => $completedAt,
                    'created_at'        => $enrolledAt,
                    'updated_at'        => $faker->dateTimeBetween($enrolledAt, 'now'),
                ]);

                $enrollmentsCreated++;
            }
        }

        echo "Successfully created {$enrollmentsCreated} enrollment records.\n";
    }
}
