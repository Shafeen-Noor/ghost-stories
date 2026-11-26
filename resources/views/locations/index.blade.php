<x-site-layout title="Places in the archive">
    <section class="archive">
        <p class="eyebrow">Follow the stories</p><h1>Places in the archive</h1>
        <form method="GET" action="{{ route('locations.index') }}" class="form-actions">
            <label for="q">Search places</label><input id="q" name="q" value="{{ $search }}" maxlength="80"><button>Search</button>
        </form>
        @error('q')<p class="error">{{ $message }}</p>@enderror
        <div class="story-grid">
            @forelse($locations as $location)
                <article class="story-card"><h2><a href="{{ route('locations.show', $location) }}">{{ $location->name }}</a></h2><p>{{ $location->description }}</p><small>{{ $location->stories_count }} {{ Str::plural('story', $location->stories_count) }}</small></article>
            @empty<p>No places match this search.</p>@endforelse
        </div>
        {{ $locations->links() }}
    </section>
</x-site-layout>
