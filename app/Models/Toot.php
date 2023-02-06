<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toot extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'number_likes',
    ];

    public function parent()
    {
        return $this->belongsTo(Toot::class, 'reply_id');
    }

    public function replies()
    {
        return $this->hasMany(Toot::class, 'reply_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function isLikedBy(User $user)
    {
        return $this->likes->contains('user_id', $user->id);
    }
}
