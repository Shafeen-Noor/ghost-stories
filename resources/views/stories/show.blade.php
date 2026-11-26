<x-site-layout :title="$story->title">
    <article class="story-page">
        <a class="back-link" href="{{ route('stories.index') }}">← All stories</a>
        <p class="eyebrow">{{ Str::headline($story->category) }} · {{ $story->location->name }}</p>
        @if($story->status !== 'visible')<p class="notice">This story is hidden from the public archive. This is your private preview.</p>@endif
        <h1>{{ $story->title }}</h1>
        <p class="story-meta">Told by <a href="{{ route('authors.show', $story->author) }}">{{ $story->author->name }}</a> · <time datetime="{{ $story->created_at->toDateString() }}">{{ $story->created_at->format('F j, Y') }}</time></p>
        <div class="tags">@foreach($story->tags as $tag)<a href="{{ route('stories.index', ['tag' => $tag->id]) }}">#{{ $tag->name }}</a>@endforeach</div>
        <x-story-actions :story="$story" />
        <x-story-votes :story="$story" />
        <div class="story-body">{{ $story->body }}</div>
        <aside class="location-note"><p class="eyebrow">The place</p><h2><a href="{{ route('locations.show', $story->location) }}">{{ $story->location->name }}</a></h2><p>{{ $story->location->description }}</p></aside>
        <x-comments :story="$story" :comments="$comments" />
        <p class="end-mark" aria-hidden="true">✦</p>
    </article>
</x-site-layout>
