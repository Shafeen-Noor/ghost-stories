<?php

namespace App\Http\Controllers;

use App\Geography;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscoveryController extends Controller
{
    public function random(Request $request): RedirectResponse
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', Rule::in(array_keys(Story::CATEGORIES))],
            'country' => ['nullable', 'string', Rule::in(array_keys(app(Geography::class)->countries()))],
        ]);
        $query = Story::visible()->filter($filters);
        $previous = $request->session()->get('discovery.last_story');
        $story = (clone $query)->whereKeyNot($previous ?? 0)->inRandomOrder()->first() ?? $query->inRandomOrder()->first();
        if (! $story) {
            return redirect()->route('stories.index')->with('status', 'No mysteries match yet. Try another category or country.');
        }
        $request->session()->put('discovery.last_story', $story->id);

        return redirect()->route('stories.show', $story)->with('status', 'Somewhere strange, chosen for you.');
    }
}
