<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursesModel extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $primaryKey = 'course_id';

    public $timestamps = true;

    protected $fillable = [
        "title",
        "description",
        "level",
        "topics",
        "video_url",
        "image_url",
        "language",
        "order",
    ];

    protected $casts = [
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sections()
    {
        return $this->hasMany(SectionsModel::class, 'course_id', 'course_id');
    }


    public function userProgress()
    {
        return $this->hasMany(UserProgressModel::class, 'course_id', 'course_id');
    }

    // enrollments
    public function enrollments()
    {
        return $this->hasMany(EnrollmentsModel::class, 'course_id', 'course_id');
    }

    public function pricing()
    {
        return $this->hasOne(CoursePricingModel::class, 'course_id', 'course_id');
    }

    public function ratings()
    {
        return $this->hasMany(CourseRatingsModel::class, 'course_id', 'course_id');
    }
    
}
