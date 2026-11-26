<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function update(Request $request, Comment $comment): RedirectResponse
    {
        $comment->update($request->validate(['status' => ['required', Rule::in(['visible', 'hidden'])]]));

        return back()->with('status', 'Comment visibility updated.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('status', 'Comment deleted.');
    }
}
