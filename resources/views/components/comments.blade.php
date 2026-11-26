@props(['story', 'comments'])
<section class="comments" id="comments">
    <h2>Around the campfire</h2>
    <p>{{ $comments->total() }} {{ Str::plural('comment', $comments->total()) }}</p>
    @forelse($comments as $comment)
        <article class="comment">
            <strong>{{ $comment->author->name }}</strong>
            <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->format('M j, Y') }}</time>
            <p class="comment-body">{{ $comment->body }}</p>
            @can('delete', $comment)
                <form method="POST" action="{{ route('comments.destroy', $comment) }}">
                    @csrf @method('DELETE')
                    <button class="delete-button" type="submit">Delete comment</button>
                </form>
            @endcan
        </article>
    @empty
        <p>No comments yet. What do you think happened?</p>
    @endforelse
    {{ $comments->links() }}
    @auth
        <form method="POST" action="{{ route('comments.store', $story) }}">
            @csrf
            <x-form-textarea name="body" label="Add a comment" :rows="3" :required="true" minlength="3" maxlength="3000" />
            <button type="submit">Post comment</button>
        </form>
    @else
        <p><a href="{{ route('login') }}">Log in</a> to join the conversation.</p>
    @endauth
</section>
