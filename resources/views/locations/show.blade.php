<x-site-layout :title="$location->name">
    <section class="archive">
        <a class="back-link" href="{{ route('locations.index') }}">← All places</a>
        <h1>{{ $location->name }}</h1><p class="intro">{{ $location->description }}</p>
        @if($location->latitude !== null && $location->longitude !== null)
            <iframe class="place-map" title="Map of {{ $location->name }}" loading="lazy" referrerpolicy="no-referrer" src="https://www.openstreetmap.org/export/embed.html?{{ http_build_query(['bbox' => implode(',', [$location->longitude - .025, $location->latitude - .025, $location->longitude + .025, $location->latitude + .025]), 'layer' => 'mapnik', 'marker' => $location->latitude.','.$location->longitude]) }}"></iframe>
            <p class="field-help">Map data © OpenStreetMap contributors</p>
        @endif
        <h2>Stories from this place</h2>
        <div class="story-grid">@forelse($stories as $story)<x-story-card :story="$story" />@empty<p>No public stories here yet.</p>@endforelse</div>
        {{ $stories->links() }}
    </section>
</x-site-layout>
