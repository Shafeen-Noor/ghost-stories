<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function store(Request $request, Story $story): RedirectResponse
    {
        abort_unless($story->status === 'visible', 404);
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:3', 'max:3000'],
            'kind' => ['nullable', 'string', Rule::in(['comment', 'explanation', 'experience'])],
            'parent_id' => ['nullable', 'integer', Rule::exists('comments', 'id')->where(fn ($query) => $query->where('story_id', $story->id)->where('status', 'visible')->whereNull('parent_id'))],
        ]);
        $validated['kind'] = ! empty($validated['parent_id']) ? 'comment' : ($validated['kind'] ?? 'comment');
        $story->comments()->create($validated + ['author_id' => $request->user()->id]);

        return redirect()->to(route('stories.show', $story).'#comments')->with('status', 'Your comment was posted.');
    }

    public function support(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($comment->status === 'visible' && $comment->story?->status === 'visible' && $comment->kind === 'explanation', 404);
        abort_if($comment->author_id === $request->user()->id, 403);
        if ($request->isMethod('delete')) {
            $comment->supporters()->detach($request->user()->id);
        } else {
            $comment->supporters()->syncWithoutDetaching([$request->user()->id]);
        }

        return back()->with('status', 'Your explanation vote was updated.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete', $comment);
        $comment->delete();

        return back()->with('status', 'Your comment was deleted.');
    }
}
