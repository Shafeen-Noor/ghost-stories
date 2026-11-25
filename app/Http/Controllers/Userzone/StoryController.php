<?php

namespace App\Http\Controllers\Userzone;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoryRequest;
use App\Models\Location;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function index(Request $request): View
    {
        $stories = $request->user()->stories()->with(['author', 'location'])->latest()->get();

        return view('userzone.stories.index', compact('stories'));
    }

    public function create(): View
    {
        $locations = Location::orderBy('name')->get();

        return view('userzone.stories.create', compact('locations'));
    }

    public function store(StoryRequest $request): RedirectResponse
    {
        $story = $request->user()->stories()->create($request->validated());

        return redirect()->route('stories.show', $story)->with('status', 'Your story is now out in the world.');
    }

    public function edit(Story $story): View
    {
        Gate::authorize('update', $story);

        $locations = Location::orderBy('name')->get();

        return view('userzone.stories.edit', compact('story', 'locations'));
    }

    public function update(StoryRequest $request, Story $story): RedirectResponse
    {
        $story->update($request->validated());

        return redirect()->route('userzone.stories.index')->with('status', 'Your story has been updated.');
    }

    public function destroy(Story $story): RedirectResponse
    {
        Gate::authorize('delete', $story);

        $story->delete();

        return redirect()->route('userzone.stories.index')->with('status', 'Your story has been deleted.');
    }
}
