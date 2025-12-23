<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token', // Required for session/web guards
    ];

    // app/Models/Admin.php

    public function latestToken()
    {
        // Returns the most recently used token for this admin
        return $this->morphOne(PersonalAccessToken::class, 'tokenable')
            ->latest('last_used_at');
    }
}
