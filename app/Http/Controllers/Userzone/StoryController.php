<?php

namespace App\Http\Controllers\Userzone;

use App\Geography;
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
        $stories = $request->user()->stories()->withVotingFor($request->user())->with(['author', 'location'])->latest()->get();

        return view('userzone.stories.index', compact('stories'));
    }

    public function create(Geography $geography): View
    {
        $locations = Location::orderBy('name')->get();

        return view('userzone.stories.create', compact('locations', 'geography'));
    }

    public function store(StoryRequest $request): RedirectResponse
    {
        $story = $request->user()->stories()->create($this->storyAttributes($request));

        return redirect()->route('stories.show', $story)->with('status', 'Your story is now out in the world.');
    }

    public function edit(Geography $geography, Story $story): View
    {
        Gate::authorize('update', $story);

        $locations = Location::orderBy('name')->get();

        return view('userzone.stories.edit', compact('story', 'locations', 'geography'));
    }

    public function update(StoryRequest $request, Story $story): RedirectResponse
    {
        $story->update($this->storyAttributes($request));

        return redirect()->route('userzone.stories.index')->with('status', 'Your story has been updated.');
    }

    public function destroy(Story $story): RedirectResponse
    {
        Gate::authorize('delete', $story);

        $story->delete();

        return redirect()->route('userzone.stories.index')->with('status', 'Your story was moved to the trash. You can restore it.');
    }

    /** @return array<string, mixed> */
    private function storyAttributes(StoryRequest $request): array
    {
        $validated = $request->validated();

        if (! empty($validated['country_code'])) {
            $validated['location_id'] = Location::fromGeography(
                $validated['country_code'], $validated['region_code'] ?? '',
            )->id;
        }

        unset($validated['country_code'], $validated['region_code']);

        return $validated;
    }
}
