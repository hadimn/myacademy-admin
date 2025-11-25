<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBadgesModel extends Model
{
    protected $table = "user_badges";

    protected $primaryKey = "user_badge_id";

    public $timestamps = true;

    protected $fillable = [
        "user_id",
        "badge_id",
        "earned_at",
    ];

    protected $casts = [
        "user_id" => "integer",
        "badge_id" => "integer",
        "earned_at" => "datetime",
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function badge(){
        return $this->belongsTo(BadgesModel::class, 'badge_id', 'badge_id');
    }
}
