@props(['story', 'comments'])
<section class="comments" id="comments" aria-label="Comments">
    <p class="eyebrow">THE INVESTIGATION CONTINUES</p><h2>Comments<span class="comment-number">{{ $comments->total() }}</span></h2>
    @if($story->status === 'visible')
        @auth
            <form method="POST" action="{{ route('comments.store', $story) }}" class="comment-composer">@csrf<div class="mini-byline"><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><strong>{{ auth()->user()->name }}</strong></div>@error('parent_id')<p class="error">{{ $message }}</p>@enderror<x-form-textarea name="body" label="Add a comment" :rows="4" :required="true" minlength="3" maxlength="3000" placeholder="What do you think happened? Share your perspective..." /><div class="composer-footer"><small>Be curious. Be respectful.</small><button type="submit">Post comment ↗</button></div></form>
        @else
            <div class="comment-signin"><p>Have a theory? Join the conversation.</p><a class="button" href="{{ route('login') }}">Log in to comment ↗</a><a href="{{ route('register') }}">Create an account</a></div>
        @endauth
    @endif
    @forelse($comments as $comment)
        <x-thread-entry :comment="$comment" :story="$story" />
    @empty<p class="empty-comments">No comments yet. Start the conversation.</p>@endforelse
    {{ $comments->links() }}
</section>
