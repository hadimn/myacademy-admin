<?php

namespace App\Models;

use App\Services\StreakService;
use Illuminate\Database\Eloquent\Model;

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
}
