<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class VerifyEmailWithOtp extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $otp = Cache::pull('user_otp');

        return (new MailMessage)
            ->subject('Your One-Time Password (OTP)')
            ->view('emails.verify-otp', [
                'otp' => $otp,
                'name' => $notifiable->name,
            ]);
    }
}
