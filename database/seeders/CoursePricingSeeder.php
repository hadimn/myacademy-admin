<?php

namespace Database\Seeders;

use App\Models\CoursePricingModel;
use App\Models\CoursesModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CoursePricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // 1. Get the IDs of all existing courses
        $courseIds = CoursesModel::pluck('course_id')->all();

        if (empty($courseIds)) {
            echo "Skipping CoursePricingsSeeder: No courses found in the 'courses_models' table. Please ensure courses are seeded first.\n";
            return;
        }

        // Use a subset of unique course IDs to ensure we don't try to create multiple pricings 
        // for the same course in this run (if the course has a one-to-one relationship with pricing).
        // We will loop through the unique IDs.
        foreach ($courseIds as $courseId) {

            $isFree = $faker->boolean(25); // 25% chance of being free
            $basePrice = $isFree ? 0.00 : $faker->randomFloat(2, 19.99, 199.99);

            $discountPrice = null;
            $discountExpiresAt = null;

            // Apply a discount 40% of the time, only if it's not a free course
            if (!$isFree && $faker->boolean(40)) {
                // Discount is between 10% and 50% off the base price
                $discountRate = $faker->randomFloat(2, 0.10, 0.50);
                $discountPrice = $basePrice * (1 - $discountRate);
                $discountPrice = round($discountPrice, 2); // Round to 2 decimal places

                // Discount expires sometime in the next 1 to 90 days
                $discountExpiresAt = $faker->dateTimeBetween('now', '+90 days');
            }

            CoursePricingModel::create([
                'course_id'           => $courseId,
                'price'               => $basePrice,
                'is_free'             => $isFree,
                'discount_price'      => $discountPrice,
                'discount_expires_at' => $discountExpiresAt,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }

        echo "Successfully created " . count($courseIds) . " pricing records for existing courses.\n";
    }
}
