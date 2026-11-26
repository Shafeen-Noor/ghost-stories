<x-site-layout title="Moderation">
<section class="archive admin-page">
    <p class="eyebrow">Keep the campfire welcoming</p><h1>Moderation</h1>
    <nav class="section-nav" aria-label="Moderation sections"><a href="#stories">Stories</a><a href="#comments">Comments</a><a href="#users">Users</a><a href="#places">Places</a><a href="#tags">Tags</a></nav>
    @foreach($errors->all() as $error)<p class="error" role="alert">{{ $error }}</p>@endforeach
    <section id="stories"><h2>Stories</h2>
    @foreach($stories as $story)
        <article class="admin-record"><h3>{{ $story->title }}</h3><p>{{ $story->author->name }} · {{ $story->trashed() ? 'In trash' : $story->status }}</p>
        <details><summary>Read story</summary><p class="comment-body">{{ $story->body }}</p></details>
        @if($story->trashed())
            <form method="POST" action="{{ route('admin.stories.restore', $story) }}">@csrf @method('PATCH')<button>Restore story</button></form>
        @else
            <div class="form-actions">
                <form method="POST" action="{{ route('admin.stories.update', $story) }}">@csrf @method('PATCH')<label for="story-{{ $story->id }}">Visibility</label><select id="story-{{ $story->id }}" name="status"><option value="visible" @selected($story->status === 'visible')>Visible</option><option value="hidden" @selected($story->status === 'hidden')>Hidden</option></select><button>Save visibility</button></form>
                <form method="POST" action="{{ route('admin.stories.destroy', $story) }}">@csrf @method('DELETE')<button class="delete-button">Move to trash</button></form>
            </div>
        @endif
        </article>
    @endforeach
    {{ $stories->links() }}</section>
    <section id="comments"><h2>Comments</h2>
    @forelse($comments as $comment)
        <article class="admin-record"><p><strong>{{ $comment->author->name }}</strong> · {{ $comment->story?->title ?? 'Story in trash' }}</p><p class="comment-body">{{ $comment->body }}</p>
            <div class="form-actions"><form method="POST" action="{{ route('admin.comments.update', $comment) }}">@csrf @method('PATCH')<label for="comment-{{ $comment->id }}">Visibility</label><select id="comment-{{ $comment->id }}" name="status"><option value="visible" @selected($comment->status === 'visible')>Visible</option><option value="hidden" @selected($comment->status === 'hidden')>Hidden</option></select><button>Save visibility</button></form>
            <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Delete this comment permanently?');">@csrf @method('DELETE')<button class="delete-button">Delete comment</button></form></div>
        </article>
    @empty<p>No comments to moderate.</p>@endforelse
    {{ $comments->links() }}</section>
    <section id="users"><h2>Users</h2>
    @foreach($users as $user)
        <article class="admin-record"><h3>{{ $user->name }}</h3><p>{{ $user->email }} · {{ $user->is_admin ? 'Administrator' : ($user->is_banned ? 'Suspended' : 'Active') }}</p>
        @unless($user->is_admin)<form method="POST" action="{{ route('admin.users.update', $user) }}">@csrf @method('PATCH')<input type="hidden" name="is_banned" value="{{ $user->is_banned ? 0 : 1 }}"><button>{{ $user->is_banned ? 'Restore access' : 'Suspend account' }}</button></form>@endunless
        </article>
    @endforeach
    {{ $users->links() }}</section>
    <section id="places"><h2>Places</h2>
    @foreach($locations as $location)
        <article class="admin-record"><h3>{{ $location->name }}</h3>
            <form method="POST" action="{{ route('admin.locations.update', $location) }}">@csrf @method('PATCH')
                <label for="description-{{ $location->id }}">Description</label><textarea id="description-{{ $location->id }}" name="description" rows="2" maxlength="2000">{{ $location->description }}</textarea>
                <div class="form-row"><div><label for="lat-{{ $location->id }}">Latitude (optional)</label><input id="lat-{{ $location->id }}" type="number" name="latitude" min="-90" max="90" step="any" value="{{ $location->latitude }}"></div><div><label for="lon-{{ $location->id }}">Longitude (optional)</label><input id="lon-{{ $location->id }}" type="number" name="longitude" min="-180" max="180" step="any" value="{{ $location->longitude }}"></div></div>
                <button>Save place details</button>
            </form>
            <form method="POST" action="{{ route('admin.locations.destroy', $location) }}">@csrf @method('DELETE')<button class="delete-button">Remove unused place</button></form>
        </article>
    @endforeach
    {{ $locations->links() }}</section>
    <section id="tags"><h2>Tags</h2>
        <form method="POST" action="{{ route('admin.tags.store') }}">@csrf<x-form-textinput name="name" label="New tag name" :required="true" maxlength="40" /><button>Add tag</button></form>
        @foreach($tags as $tag)
            <article class="admin-record"><div class="form-actions"><form method="POST" action="{{ route('admin.tags.update', $tag) }}">@csrf @method('PATCH')<label for="tag-{{ $tag->id }}">Tag name</label><input id="tag-{{ $tag->id }}" name="name" value="{{ $tag->name }}" required maxlength="40"><button>Rename tag</button></form>
                <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}">@csrf @method('DELETE')<button class="delete-button">Remove tag</button></form></div>
            </article>
        @endforeach
        {{ $tags->links() }}
    </section>
</section>
</x-site-layout>
