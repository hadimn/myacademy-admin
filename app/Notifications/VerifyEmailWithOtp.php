<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class VerifyEmailWithOtp extends Notification
{
    use Queueable;

    protected $otp;

    // Pass OTP when creating the notification
    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        // Build verification link
        $frontendUrl = config('app.frontend_url');
        $verificationUrl = $frontendUrl . "/auth/verify?userId={$notifiable->id}&email={$notifiable->email}";
        $myotp = Cache::pull('user_otp');
        Cache::delete('user_otp');

        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting("Hello {$notifiable->name},")
            ->line('Please verify your email to activate your account.')
            ->line("Your OTP is: {$myotp}")
            ->action('Verify Email', $verificationUrl) // <-- this is the clickable button
            ->line('If you did not create an account, no action is required.');
    }
}
