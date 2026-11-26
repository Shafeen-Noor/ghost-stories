<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Location;
use App\Models\Story;
use App\Models\Tag;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stories' => Story::withTrashed()->with('author')->latest()->paginate(10, ['*'], 'stories_page'),
            'comments' => Comment::with(['author', 'story'])->latest()->paginate(10, ['*'], 'comments_page'),
            'users' => User::orderBy('name')->paginate(10, ['*'], 'users_page'),
            'locations' => Location::orderBy('name')->paginate(10, ['*'], 'locations_page'),
            'tags' => Tag::orderBy('name')->paginate(20, ['*'], 'tags_page'),
        ]);
    }
}
