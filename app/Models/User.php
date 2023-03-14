<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    protected function avatar(): Attribute {
        return Attribute::make(get: function($value) {
            return $value
                ? '/storage/avatars/' . $value
                : '/fallback-avatar.jpg';
        });
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function feedPosts() {
        return $this->hasManyThrough(
            Post::class, // final model we want
            Follow::class, // intermediate table
            'user_id', // foreign key on the through table
            'user_id', // foreign key on the final table
            'id', // local key
            'followeduser' // local key on the intermediate table
        );
    }

    public function followers() {
        return $this->hasMany(Follow::class, 'followeduser', 'id');
    }

    public function followingTheseUsers() {
        return $this->hasMany(Follow::class, 'user_id', 'id');
    }

    public function posts() {
        return $this->hasMany(Post::class, 'user_id');
    }
}
