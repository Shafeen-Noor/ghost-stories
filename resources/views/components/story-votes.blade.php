@props(['story'])

@php($currentVote = $story->votes->first()?->value)
<div class="story-votes" role="group" aria-label="Votes for {{ $story->title }}">
    <span class="vote-count">Truth score: {{ $story->upvotes_count - $story->downvotes_count }} · {{ $story->upvotes_count }} up · {{ $story->downvotes_count }} down</span>
    @can('vote', $story)
        <form method="POST" action="{{ route('stories.vote.store', $story) }}">
            @csrf
            <button class="vote-button" name="value" value="1" aria-pressed="{{ $currentVote === 1 ? 'true' : 'false' }}" aria-label="Upvote {{ $story->title }}">↑ Upvote</button>
            <button class="vote-button" name="value" value="-1" aria-pressed="{{ $currentVote === -1 ? 'true' : 'false' }}" aria-label="Downvote {{ $story->title }}">↓ Downvote</button>
        </form>
        @if($currentVote !== null)
            <form method="POST" action="{{ route('stories.vote.destroy', $story) }}">
                @csrf
                @method('DELETE')
                <button class="remove-vote" type="submit">Remove vote</button>
            </form>
        @endif
    @else
        @guest
            <a href="{{ route('login') }}" class="vote-login">Log in to vote</a>
        @else
            <span class="field-help">You can't vote on your own story.</span>
        @endguest
    @endcan
</div>
