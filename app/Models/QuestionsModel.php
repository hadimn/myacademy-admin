<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionsModel extends Model
{
    protected $table = 'questions';

    protected $primaryKey = 'questions_id';

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'is_last' => 'boolean',
    ];

    public $timestamps = true;

    public function lesson()
    {
        return $this->belongsTo(LessonsModel::class, 'lesson_id');
    }

    public function answeredQuestion()
    {
        return $this->hasOne(
            AnsweredQuestionsModel::class,
            'questions_id',
            'questions_id'
        );
    }
}
