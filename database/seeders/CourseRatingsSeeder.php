<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseRatingsModel;
use App\Models\User;
use App\Models\CoursesModel;

class CourseRatingsSeeder extends Seeder
{
    public function run()
    {
        $users = User::pluck('id');
        $courses = CoursesModel::pluck('course_id');

        foreach ($courses as $courseId) {

            // Decide how many ratings this course will have
            $numberOfRatings = rand(3, min(7, $users->count()));

            // Pick UNIQUE users
            $selectedUsers = $users->shuffle()->take($numberOfRatings);

            foreach ($selectedUsers as $userId) {
                CourseRatingsModel::create([
                    'course_id' => $courseId,
                    'user_id'   => $userId,
                    'rating'    => rand(1, 5),
                ]);
            }
        }
    }
}
