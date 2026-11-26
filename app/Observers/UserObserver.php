<?php

namespace App\Observers;

use App\ArchiveStatistics;
use App\Models\User;

class UserObserver
{
    public function deleting(User $user): void
    {
        foreach ($user->stories()->withTrashed()->get() as $story) {
            $story->forceDelete();
        }
        $user->comments()->delete();
        $user->votes()->delete();
        app(ArchiveStatistics::class)->forget();
    }
}
