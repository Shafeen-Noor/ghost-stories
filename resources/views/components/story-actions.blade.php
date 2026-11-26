@props(['story'])

@can('update', $story)
    <div class="story-actions">
        <a href="{{ route('userzone.stories.edit', $story) }}" aria-label="Edit {{ $story->title }}">Edit story</a>
        <form method="POST" action="{{ route('userzone.stories.destroy', $story) }}" onsubmit="return confirm('Move this story to the trash? You can restore it later.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="delete-button" aria-label="Delete {{ $story->title }}">Delete story</button>
        </form>
    </div>
@endcan
