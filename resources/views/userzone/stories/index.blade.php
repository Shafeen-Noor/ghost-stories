<x-site-layout title="My stories">
    <section class="archive">
        <div class="section-heading"><div><p class="eyebrow">Your corner of the archive</p><h1>My stories</h1></div><a class="button" href="{{ route('userzone.stories.create') }}">Tell a story ↗</a></div>
        <div class="story-grid">
            @forelse($stories as $story)
                <x-story-card :story="$story" />
            @empty
                <div class="empty-state"><h2>Every storyteller starts somewhere.</h2><p>You haven't shared a story yet.</p><a href="{{ route('userzone.stories.create') }}">Write your first story →</a></div>
            @endforelse
        </div>
    </section>
</x-site-layout>
