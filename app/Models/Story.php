<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Story extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Story $story): void {
            if (blank($story->slug)) {
                $story->slug = $story->slugify();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    private function slugify(): string
    {
        $base = substr((string) str($this->title)->slug('-'), 0, 220) ?: 'story';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->whereKeyNot($this->getKey() ?? 0)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scopeWithVotingFor(Builder $query, ?User $user): void
    {
        $query->withCount([
            'votes as upvotes_count' => fn (Builder $votes): Builder => $votes->where('value', 1),
            'votes as downvotes_count' => fn (Builder $votes): Builder => $votes->where('value', -1),
        ])->with(['votes' => fn (HasMany $votes): HasMany => $votes->where('user_id', $user?->id)]);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
