<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function index(): View
    {
        $stories = Story::query()
            ->where('status', 'visible')
            ->with(['author', 'location'])
            ->latest()
            ->get();

        return view('stories.index', compact('stories'));
    }

    public function show(Story $story): View
    {
        abort_unless($story->status === 'visible', 404);

        $story->load(['author', 'location']);

        return view('stories.show', compact('story'));
    }
}
