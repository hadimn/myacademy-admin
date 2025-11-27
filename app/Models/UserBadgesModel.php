<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_badge_id
 * @property int $user_id
 * @property int $badge_id
 * @property \Illuminate\Support\Carbon $earned_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BadgesModel $badge
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel whereBadgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel whereEarnedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel whereUserBadgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserBadgesModel whereUserId($value)
 * @mixin \Eloquent
 */
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
