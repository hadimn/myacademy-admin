<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StreakBrokenNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public int $brokenStreak,
        public int $longestStreak
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('💔 Your ' . $this->brokenStreak . '-Day Streak Has Ended')
            ->greeting('Hello ' . $this->user->name . ',')
            ->line('We wanted to let you know that your **' . $this->brokenStreak . '-day** learning streak has ended.')
            ->line('## Streak Summary:')
            ->line('✅ **Streak that ended:** ' . $this->brokenStreak . ' days')
            ->line('🏆 **Your longest streak:** ' . $this->longestStreak . ' days')
            ->line('## Ready for a Comeback?')
            ->line('Every great learner has setbacks. What matters is getting back on track!')
            ->line('Start a new streak today and beat your personal best of **' . $this->longestStreak . ' days**!')
            ->action('Start New Streak', url('/courses'))
            ->line('You\'ve got this! One lesson at a time.')
            ->salutation('Believe in your journey,\nThe ' . config('app.name') . ' Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'broken_streak' => $this->brokenStreak,
            'longest_streak' => $this->longestStreak,
            'type' => 'streak_broken',
        ];
    }
}