<?php

namespace Database\Seeders;

use App\Models\BadgesModel;
use Illuminate\Database\Seeder;

class BadgesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BadgesModel::truncate();
        $badges = [
            // 🏆 STREAK BADGES
            [
                'name' => '3-Day Streak',
                'description' => 'Maintain a 3-day learning streak',
                'icon' => '🔥',
                'type' => 'streak',
                'criteria' => ['days_required' => 3],
                'points' => 50,
            ],
            [
                'name' => '1-Week Streak',
                'description' => 'Maintain a 7-day learning streak',
                'icon' => '⚡',
                'type' => 'streak',
                'criteria' => ['days_required' => 7],
                'points' => 100,
            ],
            [
                'name' => '2-Week Streak',
                'description' => 'Maintain a 14-day learning streak',
                'icon' => '🌟',
                'type' => 'streak',
                'criteria' => ['days_required' => 14],
                'points' => 200,
            ],
            [
                'name' => '1-Month Streak',
                'description' => 'Maintain a 30-day learning streak',
                'icon' => '🎯',
                'type' => 'streak',
                'criteria' => ['days_required' => 30],
                'points' => 500,
            ],

            // 📚 COURSE COMPLETION BADGES
            [
                'name' => 'First Course',
                'description' => 'Complete your first course',
                'icon' => '🎓',
                'type' => 'course_completion',
                'criteria' => ['courses_required' => 1],
                'points' => 50,
            ],
            [
                'name' => 'Course Explorer',
                'description' => 'Complete 3 courses',
                'icon' => '📖',
                'type' => 'course_completion',
                'criteria' => ['courses_required' => 3],
                'points' => 150,
            ],
            [
                'name' => 'Course Master',
                'description' => 'Complete 5 courses',
                'icon' => '🏆',
                'type' => 'course_completion',
                'criteria' => ['courses_required' => 5],
                'points' => 300,
            ],
            [
                'name' => 'Learning Champion',
                'description' => 'Complete 10 courses',
                'icon' => '👑',
                'type' => 'course_completion',
                'criteria' => ['courses_required' => 10],
                'points' => 600,
            ],

            // ⭐ POINTS BADGES
            [
                'name' => 'Points Collector',
                'description' => 'Earn 100 points from correct answers',
                'icon' => '⭐',
                'type' => 'points',
                'criteria' => ['points_required' => 100],
                'points' => 25,
            ],
            [
                'name' => 'Quick Learner',
                'description' => 'Earn 500 points from correct answers',
                'icon' => '🚀',
                'type' => 'points',
                'criteria' => ['points_required' => 500],
                'points' => 100,
            ],
            [
                'name' => 'Knowledge Seeker',
                'description' => 'Earn 1000 points from correct answers',
                'icon' => '💡',
                'type' => 'points',
                'criteria' => ['points_required' => 1000],
                'points' => 250,
            ],
            [
                'name' => 'Learning Expert',
                'description' => 'Earn 2500 points from correct answers',
                'icon' => '🧠',
                'type' => 'points',
                'criteria' => ['points_required' => 2500],
                'points' => 500,
            ],

            // 🎯 LESSON COMPLETION BADGES
            [
                'name' => 'First Lesson',
                'description' => 'Complete your first lesson',
                'icon' => '✅',
                'type' => 'lesson_completion',
                'criteria' => ['lessons_required' => 1],
                'points' => 10,
            ],
            [
                'name' => 'Lesson Explorer',
                'description' => 'Complete 10 lessons',
                'icon' => '📚',
                'type' => 'lesson_completion',
                'criteria' => ['lessons_required' => 10],
                'points' => 75,
            ],
            [
                'name' => 'Lesson Master',
                'description' => 'Complete 25 lessons',
                'icon' => '🎯',
                'type' => 'lesson_completion',
                'criteria' => ['lessons_required' => 25],
                'points' => 200,
            ],

            // ⏰ TIME SPENT BADGES
            [
                'name' => 'Dedicated Learner',
                'description' => 'Spend 1 hour learning',
                'icon' => '⏰',
                'type' => 'time_spent',
                'criteria' => ['minutes_required' => 60],
                'points' => 30,
            ],
            [
                'name' => 'Marathon Learner',
                'description' => 'Spend 5 hours learning',
                'icon' => '🏃',
                'type' => 'time_spent',
                'criteria' => ['minutes_required' => 300],
                'points' => 100,
            ],
        ];

        foreach ($badges as $badge) {
            BadgesModel::create($badge);
        }

        $this->command->info('✅ ' . count($badges) . ' badges created successfully!');
        $this->command->info('🎯 Streak Badges: 4');
        $this->command->info('📚 Course Badges: 4');
        $this->command->info('⭐ Points Badges: 4');
        $this->command->info('✅ Lesson Badges: 3');
        $this->command->info('⏰ Time Badges: 2');
    }
}
