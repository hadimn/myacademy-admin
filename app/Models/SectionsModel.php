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
    ];

    protected $casts = [
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    public function course()
    {
        $this->belongsTo(CoursesModel::class, 'course_id');
    }

    public function units()
    {
        $this->hasMany(UnitsModel::class, 'unit_id');
    }
}
