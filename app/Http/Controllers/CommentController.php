<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Story $story): RedirectResponse
    {
        abort_unless($story->status === 'visible', 404);
        $validated = $request->validate(['body' => ['required', 'string', 'min:3', 'max:3000']]);
        $story->comments()->create($validated + ['author_id' => $request->user()->id]);

        return redirect()->to(route('stories.show', $story).'#comments')->with('status', 'Your comment was posted.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);
        $comment->delete();

        return back()->with('status', 'Your comment was deleted.');
    }
}
