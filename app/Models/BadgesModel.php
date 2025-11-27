<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $badge_id
 * @property string $name
 * @property string $description
 * @property string|null $icon
 * @property string $type
 * @property array<array-key, mixed> $criteria
 * @property int $points
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserBadgesModel> $userBadges
 * @property-read int|null $user_badges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel whereBadgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel whereCriteria($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BadgesModel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
