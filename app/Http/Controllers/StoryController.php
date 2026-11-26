<?php

namespace App\Http\Controllers;

use App\ArchiveStatistics;
use App\Geography;
use App\Http\Requests\ArchiveRequest;
use App\Models\Story;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function index(ArchiveRequest $request, Geography $geography, ArchiveStatistics $statistics): View|RedirectResponse
    {
        if ($request->boolean('reset')) {
            $request->session()->forget('archive.filters');

            return redirect()->route('stories.index');
        }

        $filters = $request->filters();
        $query = Story::query()->visible()->filter($filters)
            ->withVotingFor($request->user())->with(['author', 'location', 'tags']);
        if ($filters['sort'] === 'score') {
            $query->orderByDesc('truth_score');
        }
        $stories = $query->latest()->orderByDesc('id')->paginate(9)->withQueryString();
        $tags = Tag::orderBy('name')->get();

        $stats = $statistics->get();
        $caseCount = Story::visible()->count();
        $tonight = $caseCount ? Story::visible()->with(['author', 'location'])->orderBy('id')->skip(((int) now()->format('z')) % $caseCount)->first() : null;

        return view('stories.index', compact('stories', 'filters', 'tags', 'geography', 'stats', 'tonight'));
    }

    public function show(Request $request, Story $story): View
    {
        abort_unless($story->status === 'visible', 404);

        $story->load(['author', 'location', 'tags']);
        $story->loadCount([
            'votes as upvotes_count' => fn (Builder $votes): Builder => $votes->where('value', 1),
            'votes as downvotes_count' => fn (Builder $votes): Builder => $votes->where('value', -1),
        ])->load(['votes' => fn (HasMany $votes): HasMany => $votes->where('user_id', $request->user()?->id)]);

        $comments = $story->comments()->where('status', 'visible')->whereNull('parent_id')->withCount('supporters')->with(['author', 'supporters' => fn ($query) => $query->where('users.id', $request->user()?->id), 'replies' => fn ($query) => $query->where('status', 'visible')->with('author')->oldest()])->latest()->paginate(10, ['*'], 'comments_page');

        return view('stories.show', compact('story', 'comments'));
    }
}
