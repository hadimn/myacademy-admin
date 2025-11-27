<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $current_streak
 * @property int $longest_streak
 * @property string|null $last_activity_date
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AnsweredQuestionsModel> $answeredQuestion
 * @property-read int|null $answered_question_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BadgesModel> $badges
 * @property-read int|null $badges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EnrollmentsModel> $enrollments
 * @property-read int|null $enrollments_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\OtpCodes|null $otp
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserBadgesModel> $userBadges
 * @property-read int|null $user_badges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserProgressModel> $userProgress
 * @property-read int|null $user_progress_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrentStreak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastActivityDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLongestStreak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

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
