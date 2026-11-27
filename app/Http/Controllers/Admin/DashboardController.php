<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Location;
use App\Models\Story;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $request->validate(['section' => ['nullable', 'string', Rule::in(['stories', 'mystery', 'comments', 'users', 'places', 'tags'])]]);
        $section = $request->input('section', 'stories');

        return view('admin.dashboard', [
            'section' => $section,
            'featured' => Story::visible()->where('is_featured', true)->first(),
            'candidates' => $section === 'mystery' ? Story::visible()->with('author')->latest()->paginate(10) : collect(),
            'stories' => Story::withTrashed()->with('author')->latest()->paginate(10, ['*'], 'stories_page'),
            'comments' => Comment::with(['author', 'story'])->latest()->paginate(10, ['*'], 'comments_page'),
            'users' => User::orderBy('name')->paginate(10, ['*'], 'users_page'),
            'locations' => Location::orderBy('name')->paginate(10, ['*'], 'locations_page'),
            'tags' => Tag::orderBy('name')->paginate(20, ['*'], 'tags_page'),
        ]);
    }
}
