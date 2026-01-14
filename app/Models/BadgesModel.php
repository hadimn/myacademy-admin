<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BadgesModel extends Model
{
    protected $table = "badges";

    protected $primaryKey = "badge_id";

    public $timestamps = true;

    protected $fillable = [
        "name",
        "description",
        "icon",
        "type",
        "criteria",
        "points",
    ];

    protected $casts = [
        "points" => "integer",
        "criteria" => "array",
    ];

    // Add this relationship
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges', 'badge_id', 'user_id')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    // Alternative: If you want to use the UserBadgesModel as intermediate
    public function userBadges()
    {
        return $this->hasMany(UserBadgesModel::class, 'badge_id', 'badge_id');
    }
}
