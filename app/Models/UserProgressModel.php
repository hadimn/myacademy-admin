<?php

namespace App\Models;

use App\Services\BadgeService;
use App\Services\StreakService;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $progress_id
 * @property int $user_id
 * @property int $lesson_id
 * @property bool $is_completed
 * @property int $time_spent
 * @property int $points
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\LessonsModel $lesson
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereIsCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereLessonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereProgressId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereTimeSpent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserProgressModel whereUserId($value)
 * @mixin \Eloquent
 */
class UserProgressModel extends Model
{
    protected $table = 'user_progress';

    protected $primaryKey = 'progress_id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'lesson_id',
        'is_completed',
        'time_spent',
        'points',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'lesson_id' => 'integer',
        'is_completed' => 'boolean',
        'time_spent' => 'integer',
        'points' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function lesson()
    {
        return $this->belongsTo(LessonsModel::class, 'lesson_id', 'lesson_id');
    }

    protected static function booted(){
        static::created(function ($progress){
            if($progress->is_completed){
                // update streak when lesson is completed.
                app(StreakService::class)->updateStreak($progress->user);
                // check and award badges when user complete a lesson.
                app(BadgeService::class)->checkAndAwardBadges($progress->user);
            }
        });

        static::updated(function ($progress){
            $wasJustCompleted = $progress->is_completed && $progress->getOriginal('is_completed') === false;
            if($wasJustCompleted){
                // update streak when lesson is completed after he failed first time. 
                app(StreakService::class)->updateStreak($progress->user);
                // check and award badges when user complete a lesson.
                app(BadgeService::class)->checkAndAwardBadges($progress->user);
            }
        });
    }
}
