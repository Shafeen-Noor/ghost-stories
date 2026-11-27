<?php

namespace App;

use App\Models\Story;
use Illuminate\Http\Request;

class CaseFile
{
    public function featured(): ?Story
    {
        return Story::visible()->where('is_featured', true)->with(['author', 'location'])->first();
    }

    /** @return list<string> */
    public function parts(Story $story): array
    {
        $parts = preg_split('/\R\s*\R/', trim($story->body), flags: PREG_SPLIT_NO_EMPTY) ?: [$story->body];
        if (count($parts) === 1) {
            $parts = preg_split('/(?<=[.!?])\s+(?=[A-Z])/', $parts[0], flags: PREG_SPLIT_NO_EMPTY) ?: $parts;
        }

        return array_map(fn (array $group): string => implode("\n\n", $group), array_chunk($parts, max(1, (int) ceil(count($parts) / 6))));
    }

    public function revealed(Request $request, Story $story): int
    {
        $progress = $request->session()->get('cases.progress.'.$story->id, []);
        $revealed = ($progress['revision'] ?? '') === sha1($story->body) ? (int) ($progress['revealed'] ?? 1) : 1;

        return max(1, min(count($this->parts($story)), $revealed));
    }

    public function remember(Request $request, Story $story, int $revealed): void
    {
        $request->session()->put('cases.progress.'.$story->id, ['revision' => sha1($story->body), 'revealed' => $revealed]);
    }

    /** @return array<string, mixed> */
    public function presentation(Request $request, Story $story): array
    {
        $parts = $this->parts($story);
        $revealed = $this->revealed($request, $story);
        $complete = $revealed === count($parts);
        $opinion = $request->user() ? $story->caseOpinions()->where('user_id', $request->user()->id)->where('revision', sha1($story->body))->first() : null;
        $results = $complete && $opinion ? $story->caseOpinions()->where('revision', sha1($story->body))->selectRaw('choice, count(*) as total')->groupBy('choice')->pluck('total', 'choice')->all() : null;

        return compact('story', 'parts', 'revealed', 'complete', 'opinion', 'results');
    }
}
