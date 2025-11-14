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
    ];

    protected $casts = [
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    public function section()
    {
        $this->belongsTo(SectionsModel::class, 'section_id');
    }

    public function lessons()
    {
        $this->hasMany(LessonsModel::class, 'lesson_id');
    }
}
