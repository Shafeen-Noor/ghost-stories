<?php

namespace App\Http\Controllers\Userzone;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $published = $user->stories()->visible()->count();
        $trashed = $user->stories()->onlyTrashed()->count();
        $stories = $user->stories()->withCardDataFor($user)->latest()->take(3)->get();

        $lastVisit = $request->session()->get('desk.last_visit.'.$user->id, now()->subWeek()->toIso8601String());
        $investigations = Story::visible()->whereHas('readingCollections', fn (Builder $query): Builder => $query->where('reading_collections.user_id', $user->id))
            ->with('location')->withCount(['comments as new_comments' => function (Builder $query) use ($lastVisit): void {
                $query->visibleInThread()->where('created_at', '>', $lastVisit);
            }])->orderByDesc('new_comments')->latest()->limit(6)->get();
        $request->session()->put('desk.last_visit.'.$user->id, now()->toDateTimeString());

        return view('userzone.dashboard', compact('user', 'published', 'trashed', 'stories', 'investigations'));
    }
}
