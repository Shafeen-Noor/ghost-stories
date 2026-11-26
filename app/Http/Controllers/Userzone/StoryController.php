<?php

namespace App\Http\Controllers\Userzone;

use App\Geography;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoryRequest;
use App\Models\Location;
use App\Models\Story;
use App\Models\Tag;
use App\StoryMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Throwable;

class StoryController extends Controller
{
    public function index(Request $request): View
    {
        $stories = $request->user()->stories()->withCardDataFor($request->user())->latest()->get();

        return view('userzone.stories.index', compact('stories'));
    }

    public function show(Request $request, Story $story): View
    {
        Gate::authorize('update', $story);
        $story = Story::withCardDataFor($request->user())->findOrFail($story->id);
        $story->loadCount(['visibleComments as discussion_count']);
        $comments = $story->comments()->threadsFor($request->user())->paginate(10, ['*'], 'comments_page');

        return view('stories.show', compact('story', 'comments'));
    }

    public function create(Geography $geography): View
    {
        $locations = Location::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('userzone.stories.create', compact('locations', 'geography', 'tags'));
    }

    public function store(StoryRequest $request): RedirectResponse
    {
        $story = $this->saveStory($request);

        return redirect()->route('stories.show', $story)->with('status', 'Your story is now out in the world.');
    }

    public function edit(Geography $geography, Story $story): View
    {
        Gate::authorize('update', $story);

        $locations = Location::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('userzone.stories.edit', compact('story', 'locations', 'geography', 'tags'));
    }

    public function update(StoryRequest $request, Story $story): RedirectResponse
    {
        $this->saveStory($request, $story);

        return redirect()->route('userzone.stories.index')->with('status', 'Your story has been updated.');
    }

    public function destroy(Story $story): RedirectResponse
    {
        Gate::authorize('delete', $story);

        $story->delete();

        return redirect()->route('userzone.stories.index')->with('status', 'Your story was moved to the trash. You can restore it.');
    }

    private function saveStory(StoryRequest $request, ?Story $story = null): Story
    {
        $media = new StoryMedia;
        try {
            $saved = DB::transaction(function () use ($request, $story, $media): Story {
                $attributes = $this->storyAttributes($request);
                if ($story === null) {
                    $story = $request->user()->stories()->create($attributes);
                } else {
                    $story->fill($attributes);
                }
                $media->apply($story, $request);
                $story->tags()->sync($request->validated('tags', []));

                return $story;
            });
        } catch (Throwable $exception) {
            $media->cleanUp(false);
            throw $exception;
        }
        $media->cleanUp(true);

        return $saved;
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

        unset($validated['country_code'], $validated['region_code'], $validated['tags'], $validated['cover_image'], $validated['background_image'], $validated['gallery'], $validated['remove_cover'], $validated['remove_background'], $validated['remove_gallery']);
        foreach (['cover_preset', 'backdrop', 'reading_theme', 'cover_layout', 'cover_position'] as $option) {
            if (array_key_exists($option, $validated) && $validated[$option] === null) {
                unset($validated[$option]);
            }
        }

        return $validated;
    }
}
