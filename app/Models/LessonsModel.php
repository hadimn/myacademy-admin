<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonsModel extends Model
{
    protected $table = 'lessons';

    protected $primaryKey = 'lesson_id';

    protected $fillable = [
        "unit_id",
        "title",
        "description",
        "content",
        "video_url",
        "image_url",
        "duration",
        "lesson_type",
        "chest_after"
    ];

    protected $casts = [
        'duration' => 'integer',
        'chest_after' => 'boolean',
        'create_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        "lesson_type" => "normal",
        "chest_after" => 0
    ];

    public $timestamps = true;

    public function unit()
    {
        return $this->belongsTo(UnitsModel::class, 'unit_id');
    }

    public function questions()
    {
        return $this->hasMany(QuestionsModel::class, 'lesson_id');
    }
}
