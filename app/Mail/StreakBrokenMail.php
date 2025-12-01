<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StreakBrokenMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public int $previousStreak,
        public int $longestStreak,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your {$this->previousStreak}-Day Streak Has Been Reset 😔",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.streak-broken',
            with: [
                'name' => $this->user->name,
                'previousStreak' => $this->previousStreak,
                'longestStreak' => $this->longestStreak,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}