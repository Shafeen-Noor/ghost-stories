<?php

namespace App\Http\Controllers\Userzone;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'body' => ['required', 'string', 'min:20', 'max:30000'],
            'category' => ['required', Rule::in(['ghost', 'urban_legend', 'conspiracy'])],
            'location_id' => ['required', 'exists:locations,id'],
        ]);

        $story = $request->user()->stories()->create($validated);

        return redirect()->route('stories.show', $story)->with('status', 'Your story is now out in the world.');
    }
}
