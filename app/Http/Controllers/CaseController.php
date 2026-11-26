<?php

namespace App\Http\Controllers;

use App\CaseFile;
use App\Models\CaseOpinion;
use App\Models\Story;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CaseController extends Controller
{
    public function tonight(): RedirectResponse
    {
        $count = Story::visible()->count();
        if ($count === 0) {
            return redirect()->route('stories.index')->with('status', 'The next case is waiting for its first storyteller.');
        }
        $story = Story::visible()->orderBy('id')->skip(((int) now()->format('z')) % $count)->first();

        return redirect()->route('cases.show', $story);
    }

    public function show(Request $request, Story $story, CaseFile $file): View
    {
        abort_unless($story->status === 'visible', 404);
        $story->load(['author', 'location', 'tags']);
        $file->remember($request, $story, $file->revealed($request, $story));
        $trail = Story::visible()->relatedTo($story)->with('location')->orderBy('id')->limit(3)->get();

        return view('cases.show', $file->presentation($request, $story) + compact('trail'));
    }

    public function reveal(Request $request, Story $story, CaseFile $file): JsonResponse|RedirectResponse
    {
        abort_unless($story->status === 'visible', 404);
        $file->remember($request, $story, min(count($file->parts($story)), $file->revealed($request, $story) + 1));

        return $this->respond($request, $story, $file);
    }

    public function opinion(Request $request, Story $story, CaseFile $file): JsonResponse|RedirectResponse
    {
        abort_unless($story->status === 'visible', 404);
        $data = $request->validate(['choice' => ['required', 'string', Rule::in(array_keys(CaseOpinion::CHOICES))]]);
        abort_unless($file->revealed($request, $story) === count($file->parts($story)), 409, 'Reveal the complete account before choosing an interpretation.');
        $story->caseOpinions()->updateOrCreate(['user_id' => $request->user()->id], ['choice' => $data['choice'], 'revision' => sha1($story->body)]);

        return $this->respond($request, $story, $file);
    }

    public function trail(Story $story): View
    {
        abort_unless($story->status === 'visible', 404);
        $story->load('location');
        $trail = Story::visible()->relatedTo($story)->with('location')->orderBy('id')->paginate(12);

        return view('cases.trail', compact('story', 'trail'));
    }

    private function respond(Request $request, Story $story, CaseFile $file): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['html' => view('cases.progress', $file->presentation($request, $story))->render()]);
        }

        return redirect()->to(route('cases.show', $story).'#case-progress');
    }
}
