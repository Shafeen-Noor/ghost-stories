<?php

namespace App\Providers;

use App\Geography;
use App\Models\Story;
use App\Models\User;
use App\Models\Vote;
use App\Observers\StoryObserver;
use App\Observers\VoteObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Geography::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->is_admin && ! $user->is_banned);
        Story::observe(StoryObserver::class);
        Vote::observe(VoteObserver::class);
        Paginator::defaultView('components.pagination');
    }
}
