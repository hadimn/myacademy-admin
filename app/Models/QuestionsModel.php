<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionsModel extends Model
{
    protected $table = 'questions';

    protected $primaryKey = 'question_id';

    protected $guarded = [];

    public $timestamps = true;

    public function lesson()
    {
        return $this->belongsTo(LessonsModel::class, 'lesson_id');
    }
}
