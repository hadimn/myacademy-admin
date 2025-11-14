<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        VerifyEmail::toMailUsing(function ($notifiable, $url, Request $request) {
            $otp = rand(100000, 999999);

            $user = $request->user();

            // If you have a method on your User model to store and retrieve the OTP:
            // $otp = $notifiable->generateAndStoreOtp();

            return (new MailMessage)
                ->greeting('hello dear!')
                ->subject('Your One-Time Password (OTP) for Verification')
                ->line('You are receiving this email because you registered on our platform. Please use the following One-Time Password (OTP) to complete your email verification.')
                ->line(new HtmlString('<div style="background-color: #f7f7f7; padding: 15px; border-radius: 8px; text-align: center; margin: 20px 0;">'))
                ->line(new HtmlString('<p style="font-size: 16px; color: #555;">Your Verification Code:</p>'))
                ->line(new HtmlString('<strong style="text-align:center;font-size: 32px; letter-spacing: 5px; color: #1f2937;">' . $otp . '</strong>'))
                ->line(new HtmlString('</div>'))
                ->line('This code will expire in a few minutes. Please enter it on the verification screen to proceed.')
                ->line('If you did not request this verification, you may safely ignore this email.');
        });
    }
}
