<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function scopeVisibleInThread(Builder $query): void
    {
        $query->where('status', 'visible')->where(fn (Builder $visible): Builder => $visible->whereNull('parent_id')
            ->orWhereHas('parent', fn (Builder $parent): Builder => $parent->where('status', 'visible')));
    }

    public function scopeThreadsFor(Builder $query, ?User $reader): void
    {
        $query->visibleInThread()->whereNull('parent_id')->withCount('supporters')->with([
            'author',
            'supporters' => fn (BelongsToMany $supporters): BelongsToMany => $supporters->where('users.id', $reader?->id),
            'replies' => fn (HasMany $replies): HasMany => $replies->visibleInThread()->with('author')->oldest(),
        ])->latest();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function supporters(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
