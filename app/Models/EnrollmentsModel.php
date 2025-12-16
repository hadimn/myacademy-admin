<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentsModel extends Model
{
    protected $table = 'enrollments';

    protected $primaryKey = "enrollment_id";

    public $timestamps = true;

    protected $fillable = [
        "user_id",
        "course_id",
        "amount_paid",
        "payment_status",
        "payment_method",
        "transaction_id",
        "enrolled_at",
        "completed_at",
    ];

    protected $casts = [
        "amount_paid" => "integer",
        "transaction_id"=>"integer",
        "enrolled_at" => "datetime",
        "completed_at" => "datetime",
    ];

    public function course(){
        return $this->belongsTo(CoursesModel::class, 'course_id', 'course_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
