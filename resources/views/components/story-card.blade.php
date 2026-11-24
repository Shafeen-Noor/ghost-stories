@props(['story'])

<article class="story-card">
    <div class="card-top"><span class="category">{{ Str::headline($story->category) }}</span><span class="card-number" aria-hidden="true">No. {{ str_pad($story->id, 3, '0', STR_PAD_LEFT) }}</span></div>
    <p class="place">{{ $story->location->name }}</p>
    <h3><a href="{{ route('stories.show', $story) }}">{{ $story->title }}</a></h3>
    <p class="excerpt">{{ Str::limit($story->body, 145) }}</p>
    <div class="card-bottom"><span>{{ $story->author->name }}<br><time datetime="{{ $story->created_at->toDateString() }}">{{ $story->created_at->format('M j, Y') }}</time></span><a class="read-link" href="{{ route('stories.show', $story) }}" aria-label="Read {{ $story->title }}">Read story <span aria-hidden="true">↗</span></a></div>
</article>
