<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class TestStreakSystem extends Command
{
    protected $signature = 'streaks:test';
    protected $description = 'Test the streak system with sample data';

    public function handle()
    {
        $this->info('🧪 Testing streak system...');

        // Create test scenarios
        $testUsers = [
            [
                'name' => 'Active User',
                'email' => 'active@test.com',
                'last_activity' => Carbon::yesterday(),
                'current_streak' => 5
            ],
            [
                'name' => 'Forgiveness User', 
                'email' => 'forgiveness@test.com',
                'last_activity' => Carbon::now()->subDays(2),
                'current_streak' => 3
            ],
            [
                'name' => 'Broken Streak User',
                'email' => 'broken@test.com', 
                'last_activity' => Carbon::now()->subDays(3),
                'current_streak' => 7
            ]
        ];

        foreach ($testUsers as $testUser) {
            $user = User::where('email', $testUser['email'])->first();
            
            if ($user) {
                $user->last_activity_date = $testUser['last_activity'];
                $user->current_streak = $testUser['current_streak'];
                $user->save();
                
                $this->info("✅ Set up test user: {$testUser['name']}");
            }
        }

        $this->info('🎯 Now run: php artisan streaks:send-reminders');
        $this->info('⏰ Or wait for the scheduled time to see automatic processing');
        
        return Command::SUCCESS;
    }
}