<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $pricing_id
 * @property int $course_id
 * @property numeric $price
 * @property bool $is_free
 * @property numeric|null $discount_price
 * @property \Illuminate\Support\Carbon|null $discount_expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\coursesModel $course
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel whereDiscountExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel whereDiscountPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel whereIsFree($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel wherePricingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CoursePricingModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CoursePricingModel extends Model
{
    protected $table = "course_pricings";

    protected $primaryKey = "pricing_id";

    public $timestamps = true;

    protected $fillable = [
        "course_id",
        "price",
        "is_free",
        "discount_price",
        "discount_expires_at",
    ];
    // should add ":2" after decimal to make it work
    // otherwise it will show an error as following:
    // "error": "Undefined array key 1"
    protected $casts = [
        "price" => "decimal:2",
        "is_free" => "boolean",
        "discount_price" => "decimal:2",
        "discount_expires_at" => "datetime",
    ];

    public function course()
    {
        return $this->belongsTo(coursesModel::class, 'course_id', 'course_id');
    }
}
