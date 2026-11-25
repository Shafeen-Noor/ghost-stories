@props(['story'])

@can('update', $story)
    <div class="story-actions">
        <a href="{{ route('userzone.stories.edit', $story) }}" aria-label="Edit {{ $story->title }}">Edit story</a>
        <form method="POST" action="{{ route('userzone.stories.destroy', $story) }}" onsubmit="return confirm('Delete this story? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="delete-button" aria-label="Delete {{ $story->title }}">Delete story</button>
        </form>
    </div>
@endcan
