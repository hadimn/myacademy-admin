<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
