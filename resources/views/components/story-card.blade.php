@props(['story', 'manage' => false])
@php($storyUrl = route($manage ? 'userzone.stories.show' : 'stories.show', $story))
<article class="story-card">
    <div class="card-top"><time datetime="{{ $story->created_at->toDateString() }}">{{ $story->created_at->format('d M Y') }}</time><span class="category">{{ Str::headline($story->category) }}</span></div>
    <a class="card-image" href="{{ $storyUrl }}"><img src="{{ $story->coverUrl() }}" alt="Cover of {{ $story->title }}" loading="lazy" style="object-position: {{ $story->cover_position }}"><span class="image-arrow" aria-hidden="true">↗</span></a>
    <p class="place"><a href="{{ route('locations.show', $story->location) }}">{{ $story->location->name }}</a><span>{{ $story->readingMinutes() }} min read</span></p>
    <h3><a href="{{ $storyUrl }}">{{ $story->title }}</a></h3>
    <p class="excerpt">{{ $story->excerpt(150) }}</p>
    <div class="tags">@foreach($story->tags as $tag)<a href="{{ route('stories.index', ['tag' => $tag->id]) }}">#{{ $tag->name }}</a>@endforeach</div>
    <div class="card-bottom"><a class="mini-byline" href="{{ route('authors.show', $story->author) }}"><span class="avatar">{{ mb_substr($story->author->name, 0, 1) }}</span>{{ $story->author->name }}</a><a class="comment-link" href="{{ $storyUrl }}#comments">Comments ↗</a></div>
    <x-story-votes :story="$story" :compact="true" />
    @if($manage)<x-story-actions :story="$story" />@endif
</article>
