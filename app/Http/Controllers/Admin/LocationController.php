<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    public function update(Request $request, Location $location): RedirectResponse
    {
        $location->update($request->validate([
            'description' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
        ]));

        return back()->with('status', 'Place details updated.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        if (Story::withTrashed()->where('location_id', $location->id)->exists()) {
            throw ValidationException::withMessages(['location' => 'This place has stories, including stories in the trash, and cannot be removed.']);
        }
        $location->delete();

        return back()->with('status', 'Unused place removed.');
    }
}
