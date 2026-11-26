<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use HasFactory, SoftDeletes;

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

        while (static::withTrashed()->where('slug', $slug)->whereKeyNot($this->getKey() ?? 0)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('status', 'visible');
    }

    /** @param array<string, mixed> $filters */
    public function scopeFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['q'])) {
            $query->where(function (Builder $search) use ($filters): void {
                $search->where('title', 'like', '%'.$filters['q'].'%')
                    ->orWhere('body', 'like', '%'.$filters['q'].'%');
            });
        }
        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (! empty($filters['tag'])) {
            $query->whereHas('tags', fn (Builder $tags): Builder => $tags->whereKey($filters['tag']));
        }
        if (! empty($filters['country'])) {
            $query->whereHas('location', fn (Builder $places): Builder => $places->where('country_code', $filters['country']));
        }
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scopeWithVotingFor(Builder $query, ?User $user): void
    {
        $query->withSum('votes as truth_score', 'value')->withCount([
            'votes as upvotes_count' => fn (Builder $votes): Builder => $votes->where('value', 1),
            'votes as downvotes_count' => fn (Builder $votes): Builder => $votes->where('value', -1),
        ])->with(['votes' => fn (HasMany $votes): HasMany => $votes->where('user_id', $user?->id)]);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
