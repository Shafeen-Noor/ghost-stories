<?php

namespace App\Http\Controllers\Userzone;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function create(): View
    {
        return view('userzone.locations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:locations,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $location = Location::create($validated);

        return redirect()->route('userzone.stories.create', ['location' => $location->id])
            ->with('status', 'Location added. Now tell its story.');
    }
}
