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

    public function show(int $id): View
    {
        $story = Story::query()
            ->where('status', 'visible')
            ->with(['author', 'location'])
            ->findOrFail($id);

        return view('stories.show', compact('story'));
    }
}
