<?php

namespace App;

use App\Models\Story;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class ArchiveStatistics
{
    public const string KEY = 'archive.statistics';

    /** @return array{stories: int, storytellers: int, places: int, votes: int} */
    public function get(): array
    {
        return Cache::remember(self::KEY, now()->addMinutes(5), fn (): array => [
            'stories' => Story::visible()->count(),
            'storytellers' => Story::visible()->distinct()->count('author_id'),
            'places' => Story::visible()->distinct()->count('location_id'),
            'votes' => Vote::whereHas('story', fn (Builder $stories): Builder => $stories->visible())->count(),
        ]);
    }

    public function forget(): void
    {
        Cache::forget(self::KEY);
    }
}
