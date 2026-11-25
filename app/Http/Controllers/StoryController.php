<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function index(Request $request): View
    {
        $stories = Story::query()
            ->withVotingFor($request->user())
            ->where('status', 'visible')
            ->with(['author', 'location'])
            ->latest()
            ->get();

        return view('stories.index', compact('stories'));
    }

    public function show(Request $request, Story $story): View
    {
        abort_unless($story->status === 'visible', 404);

        $story->load(['author', 'location']);
        $story->loadCount([
            'votes as upvotes_count' => fn (Builder $votes): Builder => $votes->where('value', 1),
            'votes as downvotes_count' => fn (Builder $votes): Builder => $votes->where('value', -1),
        ])->load(['votes' => fn (HasMany $votes): HasMany => $votes->where('user_id', $request->user()?->id)]);

        return view('stories.show', compact('story'));
    }
}
