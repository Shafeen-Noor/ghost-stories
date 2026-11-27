<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoryController extends Controller
{
    public function feature(Request $request, Story $story): RedirectResponse
    {
        abort_unless($story->status === 'visible', 422, 'Make the story visible before featuring it.');
        $data = $request->validate(['featured' => ['required', 'boolean']]);
        DB::transaction(function () use ($story, $data): void {
            if ($data['featured']) {
                Story::withTrashed()->where('is_featured', true)->update(['is_featured' => false]);
            }
            $story->update(['is_featured' => $data['featured']]);
        });

        return back()->with('status', $data['featured'] ? 'Featured mystery updated.' : 'Mystery removed from highlights.');
    }

    public function update(Request $request, Story $story): RedirectResponse
    {
        $story->update($request->validate(['status' => ['required', 'string', Rule::in(['visible', 'hidden'])]]));

        return back()->with('status', 'Story visibility updated.');
    }

    public function destroy(Story $story): RedirectResponse
    {
        $story->delete();

        return back()->with('status', 'Story moved to the trash.');
    }

    public function restore(Story $story): RedirectResponse
    {
        abort_unless($story->trashed(), 404);
        $story->restore();

        return back()->with('status', 'Story restored.');
    }
}
