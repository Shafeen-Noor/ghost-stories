<?php

namespace App\Observers;

use App\ArchiveStatistics;
use App\Models\Vote;

class VoteObserver
{
    public function saved(Vote $model): void
    {
        app(ArchiveStatistics::class)->forget();
    }

    public function deleted(Vote $model): void
    {
        app(ArchiveStatistics::class)->forget();
    }
}
