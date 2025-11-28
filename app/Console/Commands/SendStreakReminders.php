<?php

namespace App\Console\Commands;

use App\Mail\StreakReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendStreakReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'streaks:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily streak reminders to active users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get users with an active streak who were active recently
        $users = User::where('current_streak', '>', 0)
                     ->whereNotNull('email_verified_at')
                     ->get();

        foreach ($users as $user) {
            // Send the reminder email
            Mail::to($user->email)->send(new StreakReminderMail($user, $user->current_streak, $user->longest_streak));
            $this->info("Reminder sent to: {$user->email}");
        }

        $this->info('All streak reminders sent successfully.');
    }
}