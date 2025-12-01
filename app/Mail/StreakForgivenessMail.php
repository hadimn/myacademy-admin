<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StreakForgivenessMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $currentStreak,
        public int $longestStreak,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Don't Break Your {$this->currentStreak}-Day Streak! 🛡️",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.streak-forgiveness',
            with: [
                'name' => $this->user->name,
                'currentStreak' => $this->currentStreak,
                'longestStreak' => $this->longestStreak,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}