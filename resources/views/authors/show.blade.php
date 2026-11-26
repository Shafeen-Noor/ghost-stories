<x-site-layout :title="$user->name">
    <section class="archive">
        <p class="eyebrow">A voice around the campfire</p><h1>{{ $user->name }}</h1>
        <p>Joined {{ $user->created_at->format('F Y') }} · {{ $stories->total() }} public {{ Str::plural('story', $stories->total()) }} · Community truth score: {{ $score }}</p>
        <div class="story-grid">@forelse($stories as $story)<x-story-card :story="$story" />@empty<p>This storyteller hasn't shared a public story yet.</p>@endforelse</div>
        {{ $stories->links() }}
    </section>
</x-site-layout>
