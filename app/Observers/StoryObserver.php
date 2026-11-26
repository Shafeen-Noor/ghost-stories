<?php

namespace App\Observers;

use App\ArchiveStatistics;
use App\Models\Story;

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

    public function restored(Story $model): void
    {
        app(ArchiveStatistics::class)->forget();
    }
}
