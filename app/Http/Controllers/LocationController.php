<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:80']]);
        $search = $validated['q'] ?? '';
        $locations = Location::query()->whereHas('stories', fn (Builder $stories): Builder => $stories->visible())
            ->withCount(['stories' => fn (Builder $stories): Builder => $stories->visible()])
            ->when($search !== '', fn (Builder $places): Builder => $places->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')->paginate(12)->withQueryString();

        return view('locations.index', compact('locations', 'search'));
    }

    public function show(Request $request, Location $location): View
    {
        $stories = $location->stories()->visible()->withVotingFor($request->user())
            ->with(['author', 'location', 'tags'])->orderByDesc('truth_score')->latest()->paginate(9);

        return view('locations.show', compact('location', 'stories'));
    }
}
