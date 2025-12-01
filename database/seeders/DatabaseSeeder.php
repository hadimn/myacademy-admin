<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'hadi monzer',
            'email' => 'hadimonzer1999@gmail.com',
            'password' => '@HadiMonzer123',
        ]);

        $this->call([
            CoursesSeeder::class,
            sectionsSeeder::class,
            Unitsseeder::class,
            LessonsSeeder::class,
            QuestionsSeeder::class,
            CoursePricingSeeder::class,
            EnrollmentsSeeder::class,
            BadgesSeeder::class,
            AdminSeeder::class,
            // UserBadgeSeeder::class,
            // UserProgressSeeder::class,
            // AnsweredQuestionsSeeder::class,
        ]);
        // User::factory(10)->create();
    }
}
