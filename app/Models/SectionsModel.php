<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SectionsModel extends Model
{
    protected $table = 'sections';

    protected $primaryKey = 'section_id';

    protected $fillable = [
        "course_id",
        "title",
        "description",
        "image_url",
        "order",
        "is_last",
    ];

    protected $casts = [
        "created_at" => "datetime",
        "updated_at" => "datetime",
        "order" => "integer",
        "is_last" => "boolean",
    ];

    public function course()
    {
        return $this->belongsTo(CoursesModel::class, 'course_id');
    }

    public function units()
    {
        return $this->hasMany(UnitsModel::class, 'section_id', 'section_id');
    }
}
