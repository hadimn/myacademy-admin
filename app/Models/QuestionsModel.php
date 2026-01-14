<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionsModel extends Model
{
    protected $table = 'questions';

    protected $primaryKey = 'questions_id';

    protected $fillable = [
        'lesson_id',
        'question_text',
        'question_type',
        'options',
        'correct_answer',
        'explanation',
        'points',
        'order',
        'is_last',
        'chest_after',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
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
