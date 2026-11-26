@props(['story', 'manage' => false])
@php($storyUrl = route($manage ? 'userzone.stories.show' : 'stories.show', $story))

<article class="story-card">
    <div class="card-top"><span class="category">{{ Str::headline($story->category) }}</span><span class="card-number" aria-hidden="true">No. {{ str_pad($story->id, 3, '0', STR_PAD_LEFT) }}</span></div>
    <p class="place"><a href="{{ route('locations.show', $story->location) }}">{{ $story->location->name }}</a></p>
    <h3><a href="{{ $storyUrl }}">{{ $story->title }}</a></h3>
    <div class="tags">@foreach($story->tags as $tag)<a href="{{ route('stories.index', ['tag' => $tag->id]) }}">#{{ $tag->name }}</a>@endforeach</div>
    <p class="excerpt">{{ Str::limit($story->body, 145) }}</p>
    <div class="card-bottom"><span><a href="{{ route('authors.show', $story->author) }}">{{ $story->author->name }}</a><br><time datetime="{{ $story->created_at->toDateString() }}">{{ $story->created_at->format('M j, Y') }}</time></span><a class="read-link" href="{{ $storyUrl }}" aria-label="Read {{ $story->title }}">Read story <span aria-hidden="true">↗</span></a></div>
    <x-story-votes :story="$story" />
    <x-story-actions :story="$story" />
</article>
