<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseRatingsModel extends Model
{
    protected $table = 'course_ratings';

    protected $primaryKey = 'id';

    protected $fillable = [
        'course_id',
        'user_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(CoursesModel::class, 'course_id', 'course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
