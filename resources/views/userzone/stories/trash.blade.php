<x-site-layout title="Your trash">
    <section class="archive">
        <a class="back-link" href="{{ route('userzone.stories.index') }}">← My stories</a>
        <h1>Your trash</h1>
        <p>These stories are hidden from the archive. Restore a story to bring it back with its votes.</p>
        @forelse($stories as $story)
            <article class="location-note">
                <h2>{{ $story->title }}</h2><p>{{ $story->location->name }}</p>
                <form method="POST" action="{{ route('userzone.stories.restore', $story) }}">
                    @csrf @method('PATCH')
                    <button type="submit">Restore story</button>
                </form>
            </article>
        @empty
            <p>Your trash is empty.</p>
        @endforelse
    </section>
</x-site-layout>
