<?php

namespace App\Console\Commands;

use App\Events\streaks\StreakBroken;
use App\Mail\StreakReminderMail;
use App\Mail\StreakForgivenessMail;
use App\Mail\StreakBrokenMail;
use App\Models\User;
use App\Services\StreakService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendStreakReminders extends Command
{
    protected $signature = 'streaks:send-reminders';
    protected $description = 'Automatically process streaks and send daily reminders';

    public function handle(StreakService $streakService)
    {
        $this->info('🚀 Starting automatic streak processing...');

        $today = Carbon::today();
        $totalUsers = User::count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();

        $this->info("📊 Total users: {$totalUsers}, Verified users: {$verifiedUsers}");

        $processedCount = 0;
        $reminderCount = 0;
        $forgivenessCount = 0;
        $brokenCount = 0;

        // Process all verified users
        User::whereNotNull('email_verified_at')
            ->chunkById(100, function ($users) use ($today, $streakService, &$processedCount, &$reminderCount, &$forgivenessCount, &$brokenCount) {
                foreach ($users as $user) {
                    $this->processUserStreak($user, $today, $streakService, $processedCount, $reminderCount, $forgivenessCount, $brokenCount);
                }
            });

        $this->info("✅ Automatic streak processing completed!");
        $this->info("📊 Statistics:");
        $this->info("   - Users processed: {$processedCount}");
        $this->info("   - Reminders sent: {$reminderCount}");
        $this->info("   - Forgiveness emails: {$forgivenessCount}");
        $this->info("   - Broken streak emails: {$brokenCount}");

        // If no emails were sent, show why
        if ($reminderCount === 0 && $forgivenessCount === 0 && $brokenCount === 0) {
            $this->warn("🤔 No emails were sent. Possible reasons:");
            $this->warn("   - No users have streaks > 0");
            $this->warn("   - Users were active today (no need for reminders)");
            $this->warn("   - No users missed days");
        }

        return Command::SUCCESS;
    }

    private function processUserStreak(User $user, Carbon $today, StreakService $streakService, &$processedCount, &$reminderCount, &$forgivenessCount, &$brokenCount)
    {
        $processedCount++;

        $lastActivity = $user->last_activity_date ? Carbon::parse($user->last_activity_date) : null;

        if (!$lastActivity) {
            $this->info("   👤 {$user->email}: No activity history");
            return;
        }

        $daysSinceLastActivity = $lastActivity->diffInDays($today);
        $this->info("   👤 {$user->email}: Streak: {$user->current_streak}, Last active: {$daysSinceLastActivity} days ago");

        // Check if user was active today (already processed)
        if ($daysSinceLastActivity === 0) {
            $this->info("   ✅ Active today - no action needed");
            return;
        }

        // Handle different streak scenarios
        switch ($daysSinceLastActivity) {
            case 1:
                // User was active yesterday - send regular reminder
                if ($user->current_streak > 0) {
                    Mail::to($user->email)->send(new StreakReminderMail($user, $user->current_streak, $user->longest_streak));
                    $reminderCount++;
                    $this->info("   📧 Reminder sent to: {$user->email} (Streak: {$user->current_streak})");
                } else {
                    $this->info("   ℹ️  No streak - no reminder sent");
                }
                break;

            case 2:
                // User missed one day - apply forgiveness
                if ($user->current_streak > 0) {
                    Mail::to($user->email)->send(new StreakForgivenessMail($user, $user->current_streak, $user->longest_streak));
                    $forgivenessCount++;
                    $this->info("   🛡️ Forgiveness email sent to: {$user->email} (Streak: {$user->current_streak})");
                } else {
                    $this->info("   ℹ️  No streak - no forgiveness email");
                }
                break;

            default:
                // ... inside the default case of your switch ...
                if ($daysSinceLastActivity >= 3 && $user->current_streak > 0) {
                    $previousStreak = $user->current_streak;

                    // Reset streak logic
                    $user->current_streak = 1;
                    if ($previousStreak > $user->longest_streak) {
                        $user->longest_streak = $previousStreak;
                    }
                    $user->save();

                    // SHOUT THE EVENT! 
                    // This triggers the Email AND the Nuxt popup automatically.
                    event(new StreakBroken($user, $previousStreak));

                    $brokenCount++;
                    $this->info(" ⚡ Event dispatched for: {$user->email}");
                } else {
                    $this->info("   ℹ️  No action needed - days: {$daysSinceLastActivity}, streak: {$user->current_streak}");
                }
                break;
        }
    }
}
