<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class VoteController extends Controller
{
    public function store(Request $request, Story $story): RedirectResponse
    {
        abort_unless($story->status === 'visible', 404);
        Gate::authorize('vote', $story);

        $validated = $request->validate([
            'value' => ['required', 'integer', Rule::in([-1, 1])],
        ]);

        $story->votes()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['value' => $validated['value']],
        );

        return back()->with('status', 'Your vote has been saved.');
    }

    public function destroy(Request $request, Story $story): RedirectResponse
    {
        abort_unless($story->status === 'visible', 404);
        Gate::authorize('vote', $story);

        $story->votes()->where('user_id', $request->user()->id)->delete();

        return back()->with('status', 'Your vote has been removed.');
    }
}
