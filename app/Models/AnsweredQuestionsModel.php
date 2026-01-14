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

    /**
     * Get the user that answered the question
     * Fixed: Foreign key should be 'user_id', and the owner key should be 'id'
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the question that was answered
     */
    public function question()
    {
        return $this->belongsTo(QuestionsModel::class, 'questions_id', 'questions_id');
    }
}