<?php

namespace App\Models;

use App\Services\BadgeService;
use App\Services\StreakService;
use Illuminate\Database\Eloquent\Model;

class UserProgressModel extends Model
{
    protected $table = 'user_progress';

    protected $primaryKey = 'progress_id';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'course_id',
        'section_id',
        'unit_id',
        'lesson_id',
        'is_completed',
        'time_spent',
        'points',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'course_id' => 'integer',
        'section_id' => 'integer',
        'unit_id' => 'integer',
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

    public function course(){
        return $this->belongsTo(CoursesModel::class, 'course_id', 'course_id');   
    }

    public function section(){
        return $this->belongsTo(SectionsModel::class, 'section_id', 'section_id');
    }

    public function unit(){
        return $this->belongsTo(UnitsModel::class, 'unit_id', 'unit_id');
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
