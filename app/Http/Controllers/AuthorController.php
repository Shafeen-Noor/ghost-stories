<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $stories = $user->stories()->visible()->withCardDataFor($request->user())->latest()->paginate(9);
        $score = Vote::whereHas('story', fn (Builder $stories): Builder => $stories->visible()->where('author_id', $user->id))->sum('value');

        return view('authors.show', compact('user', 'stories', 'score'));
    }
}
