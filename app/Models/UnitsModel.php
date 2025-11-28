<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitsModel extends Model
{
    protected $table = 'units';

    protected $primaryKey = 'unit_id';

    protected $fillable = [
        "section_id",
        "title",
        "color",
        "order",
        "is_last",
    ];

    protected $casts = [
        "created_at" => "datetime",
        "updated_at" => "datetime",
        "order" => "integer",
        "is_last" => "boolean",
    ];

    public function section()
    {
        return $this->belongsTo(SectionsModel::class, 'section_id');
    }

    public function lessons()
    {
        return $this->hasMany(LessonsModel::class, 'unit_id', 'unit_id');
    }

    public function userProgress(){
        return $this->hasMany(UserProgressModel::class, 'lesson_id', 'lesson_id');
    }
}
