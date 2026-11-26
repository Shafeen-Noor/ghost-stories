<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'author_id');
    }

    public function readingCollections(): HasMany
    {
        return $this->hasMany(ReadingCollection::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    protected function casts(): array
    {
        return [
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
