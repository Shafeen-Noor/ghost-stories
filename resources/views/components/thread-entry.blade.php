@props(['comment', 'story', 'reply' => false])
<article class="comment {{ $reply ? 'thread-reply' : '' }}" id="comment-{{ $comment->id }}">
    <div class="comment-avatar avatar">{{ mb_substr($comment->author->name, 0, 1) }}</div>
    <div class="comment-content">
        <div class="comment-meta"><a href="{{ route('authors.show', $comment->author) }}">{{ $comment->author->name }}</a><time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->format('M j, Y') }}</time>@if($comment->kind !== 'comment')<span class="category">{{ $comment->kind === 'explanation' ? 'Possible explanation' : 'I experienced this too' }}</span>@endif</div>
        <p class="comment-body">{{ $comment->body }}</p>
        <div class="thread-actions">
            @if($comment->kind === 'explanation')
                <span>{{ $comment->supporters_count ?? 0 }} find this convincing</span>
                @auth @if(auth()->id() !== $comment->author_id)
                    <form method="POST" action="{{ route($comment->supporters->isNotEmpty() ? 'comments.unsupport' : 'comments.support', $comment) }}">@csrf @if($comment->supporters->isNotEmpty()) @method('DELETE') @endif<button class="text-button">{{ $comment->supporters->isNotEmpty() ? 'Remove vote' : 'Convincing +1' }}</button></form>
                @endif @endauth
            @endif
            @can('delete', $comment)<form method="POST" action="{{ route('comments.destroy', $comment) }}">@csrf @method('DELETE')<button class="text-button muted">Delete comment</button></form>@endcan
        </div>
        @if(!$reply)
            @foreach($comment->replies as $child)<x-thread-entry :comment="$child" :story="$story" :reply="true" />@endforeach
            @auth<details class="reply-composer"><summary>Reply to this thread ↗</summary><form method="POST" action="{{ route('comments.store', $story) }}">@csrf<input type="hidden" name="parent_id" value="{{ $comment->id }}"><label for="reply-{{ $comment->id }}">Your reply</label><textarea id="reply-{{ $comment->id }}" name="body" required minlength="3" maxlength="3000" rows="3"></textarea><button>Post reply</button></form></details>@endauth
        @endif
    </div>
</article>
