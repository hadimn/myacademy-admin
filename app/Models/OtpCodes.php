<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use function Symfony\Component\Clock\now;

class OtpCodes extends Model
{
    protected $table = "otp_codes";

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
        "hashed_otp" => "hashed",
        "created_at" => "datetime",
        "updated_at" => "datetime",
        "expires_at" => "datetime",
    ];

    protected $attributes = [
        "is_verified" => false,
    ];

    public function user(){
        $this->belongsTo(User::class, 'users_id', 'id');
    }

    public function generateNewOtp(){
        return rand(100000, 999999);
    }

    public function verifyCode($otpInput){
        return Hash::check($otpInput, $this->hashe_otp);
    }

    public function isExpired(){
        if($this->expires_at >= now()){
            return true;
        }
        return false;
    }

    public function markAsVerified(){
        $this->is_verified = true;
        $this->save();
    }
}
