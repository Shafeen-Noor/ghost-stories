<?php

namespace App\Http\Controllers\Userzone;

use App\Geography;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function create(Geography $geography): View
    {
        return view('userzone.locations.create', compact('geography'));
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $location = Location::fromGeography($validated['country_code'], $validated['region_code'] ?? '');

        return redirect()->route('userzone.stories.create', ['location' => $location->id])
            ->with('status', 'Location selected. Now tell its story.');
    }
}
