<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class coursesModel extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $primaryKey = 'course_id';

    public $timestamps = true;

    protected $fillable = [
        "title",
        "description",
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



    public function section()
    {
        return $this->hasMany(SectionsModel::class, 'section_id');
    }
}
