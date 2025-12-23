<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnsweredQuestionsModel extends Model
{
    protected $table = "answered_questions";
    protected $primaryKey = 'answered_id';
    public $timestamps = true;
    protected $fillable = [
        'user_id',
        'questions_id',
        'earned_points',
        'is_passed',
    ];
    protected $casts = [
        "earned_points" => "integer",
        "is_passed" => "boolean"
    ];

    protected $attributes = [
        "earned_points" => 0,
        "is_passed" => false,
    ];

    public function users(){
        return $this->hasMany(User::class, 'id', 'user_id');
    }

    public function question(){
        return $this->belongsTo(QuestionsModel::class, 'questions_id', 'questions_id');
    }
}
