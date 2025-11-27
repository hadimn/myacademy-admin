<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $section_id
 * @property int $course_id
 * @property string $title
 * @property string|null $description
 * @property string|null $image_url
 * @property int $order
 * @property bool $is_last
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\coursesModel $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UnitsModel> $units
 * @property-read int|null $units_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereIsLast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SectionsModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
