<?php

namespace App\Models;

use App\StoryText;
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

    public const CATEGORIES = [
        'ghost' => 'Ghosts & encounters',
        'urban_legend' => 'Folklore & urban legends',
        'conspiracy' => 'Conspiracy theories',
    ];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Story $story): void {
            if (blank($story->slug)) {
                $story->slug = $story->slugify();
            }
        });
    }

    protected function casts(): array
    {
        return ['gallery_paths' => 'array', 'is_featured' => 'boolean'];
    }

    public function coverUrl(): string
    {
        if ($this->cover_path) {
            return route('stories.media', [$this, 'cover']);
        }
        $preset = $this->cover_preset;
        if (! in_array($preset, ['house', 'station', 'coast'], true)) {
            $preset = ['house', 'station', 'coast'][max(0, ($this->id ?? 1) - 1) % 3];
        }

        return asset('images/'.$preset.'.jpg');
    }

    public function backgroundUrl(): ?string
    {
        if ($this->background_path) {
            return route('stories.media', [$this, 'background']);
        }
        if ($this->backdrop === 'cover') {
            return $this->coverUrl();
        }

        return in_array($this->backdrop, ['house', 'station', 'coast'], true) ? asset('images/'.$this->backdrop.'.jpg') : null;
    }

    public function mediaPath(string $asset): ?string
    {
        return match (true) {
            $asset === 'cover' => $this->cover_path,
            $asset === 'background' => $this->background_path,
            preg_match('/^gallery-([0-5])$/', $asset, $matches) === 1 => ($this->gallery_paths ?? [])[(int) $matches[1]] ?? null,
            default => null,
        };
    }

    public function excerpt(int $length = 160): string
    {
        return app(StoryText::class)->excerpt($this->body, $length);
    }

    public function readingMinutes(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags(app(StoryText::class)->render($this->body))) / 200));
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

    public function caseOpinions(): HasMany
    {
        return $this->hasMany(CaseOpinion::class);
    }

    public function readingCollections(): BelongsToMany
    {
        return $this->belongsToMany(ReadingCollection::class);
    }

    public function scopeRelatedTo(Builder $query, Story $story): void
    {
        $query->whereKeyNot($story->id)->where(function (Builder $related) use ($story): void {
            $related->where('location_id', $story->location_id)->orWhere('category', $story->category)
                ->orWhereHas('tags', fn (Builder $tags): Builder => $tags->whereIn('tags.id', $story->tags()->pluck('tags.id')));
        });
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function visibleComments(): HasMany
    {
        return $this->comments()->visibleInThread();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function scopeWithCardDataFor(Builder $query, ?User $reader): void
    {
        $query->withVotingFor($reader)->with(['author', 'location', 'tags']);
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
