<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StoryController extends Controller
{
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
