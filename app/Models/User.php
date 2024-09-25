<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'bool'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'username',
        'password',
        'email',
        'email_verified_at',
        'profile_photo_path',
        'remember_token',
        'bio',
        'is_admin'
    ];

    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id')->orderBy('created_at', 'desc');
    }

    public function arts()
    {
        return $this->hasMany(Art::class, 'user_id')->orderBy('created_at', 'desc');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follower_user', 'follower_id', 'user_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follower_user', 'user_id', 'follower_id')->withTimestamps();
    }

    public function isFollowing(User $user)
    {
        return $this->following()->where('user_id', $user->id)->exists();
    }

    public function likes()
    {
        return $this->belongsToMany(Art::class, 'art_like', 'user_id', 'art_id')->withTimestamps();
    }

    public function isLikes(Art $art)
    {
        return $this->likes()->where('art_id', $art->id)->exists();
    }
}
