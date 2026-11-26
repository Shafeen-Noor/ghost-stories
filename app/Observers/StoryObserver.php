<?php

namespace App\Observers;

use App\ArchiveStatistics;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;

class StoryObserver
{
    public function saved(Story $model): void
    {
        app(ArchiveStatistics::class)->forget();
    }

    public function deleted(Story $model): void
    {
        app(ArchiveStatistics::class)->forget();
    }

    public function forceDeleted(Story $model): void
    {
        Storage::disk('local')->delete(array_filter(array_merge([$model->cover_path, $model->background_path], $model->gallery_paths ?? [])));
    }

    public function restored(Story $model): void
    {
        app(ArchiveStatistics::class)->forget();
    }
}
