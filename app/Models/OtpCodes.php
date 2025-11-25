<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

use function Symfony\Component\Clock\now;

class OtpCodes extends Model
{
    use Notifiable;

    protected $table = "otp_codes";

    protected $primaryKey = "otp_id";

    protected $fillable = [
        "user_id",
        "hashed_otp",
        "is_verified",
        "expires_at",
    ];

    protected $hidden = [
        "hashed_otp",
    ];


    protected $casts = [
        "is_verified" => "boolean",
        // "hashed_otp" => "hashed",
        "created_at" => "datetime",
        "updated_at" => "datetime",
        "expires_at" => "datetime",
    ];

    protected $attributes = [
        "is_verified" => false,
    ];

    public function user()
    {
        $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function generateNewOtp()
    {
        return rand(100000, 999999);
    }

    public function verifyCode($otpInput)
    {
        return Hash::check($otpInput, $this->hashed_otp);
    }

    public function isExpired()
    {
        if ($this->expires_at >= now()) {
            return true;
        }
        return false;
    }

    public function markAsVerified()
    {
        $this->is_verified = 1;
    }

    public static function deleteLatest($userId)
    {
        $latestOtp = self::where('user_id', $userId)
            ->where('expires_at', '<', Carbon::now())
            ->where("is_verified", false)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($latestOtp) {
            $latestOtp->delete();
            return true;
        }
        return false;
    }
}
