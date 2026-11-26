<?php

namespace App\Http\Controllers;

use App\Models\ReadingCollection;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CollectionController extends Controller
{
    public function index(Request $request): View
    {
        $collections = $request->user()->readingCollections()->withCount(['stories' => fn ($query) => $query->visible()])->latest()->get();

        return view('collections.index', compact('collections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', Rule::unique('reading_collections')->where('user_id', $request->user()->id)]]);
        $collection = $request->user()->readingCollections()->create($data);

        return redirect()->route('collections.show', $collection)->with('status', 'Collection created. Save mysteries from any story.');
    }

    public function show(Request $request, ReadingCollection $collection): View
    {
        abort_unless($collection->user_id === $request->user()->id, 403);
        $stories = $collection->stories()->visible()->withCardDataFor($request->user())->latest()->paginate(9);

        return view('collections.show', compact('collection', 'stories'));
    }

    public function update(Request $request, ReadingCollection $collection): RedirectResponse
    {
        abort_unless($collection->user_id === $request->user()->id, 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:80', Rule::unique('reading_collections')->where('user_id', $request->user()->id)->ignore($collection)]]);
        $collection->update($data);

        return back()->with('status', 'Collection renamed.');
    }

    public function destroy(Request $request, ReadingCollection $collection): RedirectResponse
    {
        abort_unless($collection->user_id === $request->user()->id, 403);
        $collection->delete();

        return redirect()->route('collections.index')->with('status', 'Collection deleted. The stories are still in the archive.');
    }

    public function save(Request $request, Story $story): RedirectResponse
    {
        abort_unless($story->status === 'visible', 404);
        $data = $request->validate([
            'collection_id' => ['nullable', 'required_without:name', 'integer', Rule::exists('reading_collections', 'id')->where('user_id', $request->user()->id)],
            'name' => ['nullable', 'required_without:collection_id', Rule::prohibitedIf($request->filled('collection_id')), 'string', 'max:80'],
        ]);
        $collection = ! empty($data['collection_id'])
            ? $request->user()->readingCollections()->findOrFail($data['collection_id'])
            : $request->user()->readingCollections()->firstOrCreate(['name' => $data['name']]);
        $collection->stories()->syncWithoutDetaching([$story->id]);

        return back()->with('status', 'Saved to '.$collection->name.'.');
    }

    public function remove(Request $request, ReadingCollection $collection, Story $story): RedirectResponse
    {
        abort_unless($collection->user_id === $request->user()->id, 403);
        $collection->stories()->detach($story->id);

        return back()->with('status', 'Story removed from this collection.');
    }
}
