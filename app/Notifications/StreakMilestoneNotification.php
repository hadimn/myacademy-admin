<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StreakMilestoneNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public int $currentStreak,
        public int $milestone
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $milestoneNames = [
            3 => '3-day Beginner',
            7 => '1-week Dedicated',
            14 => '2-week Consistent', 
            30 => '1-month Master',
            60 => '2-month Legend',
            90 => '3-month Champion'
        ];

        $title = $milestoneNames[$this->milestone] ?? $this->milestone . '-day Streak';

        return (new MailMessage)
            ->subject('🎉 Amazing! You\'ve Reached a ' . $title . ' Milestone!')
            ->greeting('Congratulations ' . $this->user->name . '! 🎊')
            ->line('## You\'ve reached **' . $this->currentStreak . ' consecutive days** of learning!')
            ->line('You\'re officially a **' . $title . '**!')
            ->line('This level of consistency is what separates good learners from great ones!')
            ->line('## Keep the Momentum Going!')
            ->line('Your dedication is inspiring. Keep up the amazing work!')
            ->action('Continue Your Journey', url('/courses'))
            ->line('Next milestone: **' . ($this->milestone + 7) . ' days**!')
            ->salutation('Proud of your progress,\nThe ' . config('app.name') . ' Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'current_streak' => $this->currentStreak,
            'milestone' => $this->milestone,
            'type' => 'streak_milestone',
        ];
    }
}