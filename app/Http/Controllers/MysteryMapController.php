<?php

namespace App\Http\Controllers;

use App\Geography;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MysteryMapController extends Controller
{
    public function __invoke(Request $request, Geography $geography): View
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(array_keys(Story::CATEGORIES))],
            'country' => ['nullable', 'string', Rule::in(array_keys($geography->countries()))],
        ]);
        $stories = Story::visible()->filter($filters)->with('location')->latest()->limit(250)->get();
        $pins = $stories->map(function (Story $story): ?array {
            $latitude = $story->latitude ?? $story->location->latitude;
            $longitude = $story->longitude ?? $story->location->longitude;
            if ($latitude === null || $longitude === null) {
                return null;
            }

            return ['id' => $story->id, 'latitude' => (float) $latitude, 'longitude' => (float) $longitude, 'title' => $story->title, 'category' => $story->category, 'place' => $story->location->name, 'url' => route('stories.show', $story)];
        })->filter()->values();
        $unmapped = $stories->count() - $pins->count();

        return view('discovery.map', compact('pins', 'filters', 'geography', 'unmapped'));
    }
}
