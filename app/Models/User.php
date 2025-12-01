<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password', 'device_token'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token', 'device_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function otp()
    {
        return $this->hasOne(OtpCodes::class, 'user_id');
    }

    public function enrollments()
    {
        return $this->hasMany(EnrollmentsModel::class, 'user_id', 'id');
    }

    public function answeredQuestion()
    {
        return $this->hasMany(AnsweredQuestionsModel::class, 'user_id', 'id');
    }


    // Add these accessors for easy streak access
    public function getCurrentStreakAttribute(): int
    {
        return $this->attributes['current_streak'] ?? 0;
    }

    public function getLongestStreakAttribute(): int
    {
        return $this->attributes['longest_streak'] ?? 0;
    }

    public function userProgress()
    {
        return $this->hasMany(UserProgressModel::class, 'user_id', 'id');
    }

    public function badges()
    {
        return $this->belongsToMany(
            BadgesModel::class,
            'user_badges',
            'user_id',
            'badge_id',
        )->withPivot('earned_at')
            ->withTimestamps();
    }

    public function userBadges()
    {
        return $this->hasMany(
            UserBadgesModel::class,
            'user_id',
            'id'
        );
    }
}
