<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StreakWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public int $currentStreak,
        public string $lastActivityDate
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysMissed = now()->diffInDays($this->lastActivityDate);
        
        return (new MailMessage)
            ->subject('🔥 Your Learning Streak is at Risk!')
            ->greeting('Hello ' . $this->user->name . '!')
            ->line('You\'re on a **' . $this->currentStreak . '-day** learning streak, but we noticed you haven\'t completed a lesson recently.')
            ->line('**Last activity:** ' . $this->lastActivityDate)
            ->line('**Days missed:** ' . $daysMissed . ' day(s)')
            ->line('## Don\'t Break Your Streak!')
            ->line('Complete a lesson today to maintain your **' . $this->currentStreak . '-day streak** and keep your learning momentum going!')
            ->action('Continue Learning', url('/courses'))
            ->line('Remember: Consistency is key to mastering new skills!')
            ->salutation('Keep learning,\nThe ' . config('app.name') . ' Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'current_streak' => $this->currentStreak,
            'last_activity' => $this->lastActivityDate,
            'type' => 'streak_warning',
        ];
    }
}