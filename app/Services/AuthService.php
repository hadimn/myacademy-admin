<?php

namespace App\Services;

use App\Http\Controllers\OtpCodeController;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Handles the user registration process, including user creation and OTP generation.
     *
     * @param array $data
     * @return User|null
     */
    public function registerUser(array $data): ?User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if (!$user) {
            return null;
        }

        $this->generateAndStoreOtp($user);

        event(new Registered($user));

        return $user;
    }

    public function authinticate(string $email, string $password): User|bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        if (!Hash::check($password, $user->password)) {
            return false;
        }

        return $user;
    }

    public function generateAndStoreOtp(User $user, int $minutes = 5)
    {
        $otp = rand(100000, 999999);
        Log::debug("my otp is: $otp");
        Cache::put('user_otp', $otp, Carbon::now()->addMinute($minutes));

        $hashed_otp = Hash::make($otp);
        $expiresAt = Carbon::now()->addMinute($minutes);

        $otpController = new OtpCodeController();
        $otpController->newOtp($user->id, $hashed_otp, $expiresAt);
    }
}
