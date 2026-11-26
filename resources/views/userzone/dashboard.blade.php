<x-site-layout title="Your campfire">
    <section class="archive">
        <p class="eyebrow">Your campfire</p><h1>Welcome, {{ $user->name }}.</h1>
        <p>{{ $published }} public stories · {{ $trashed }} stories in the trash</p>
        <div class="form-actions"><a class="button" href="{{ route('userzone.stories.create') }}">Tell a story</a><a href="{{ route('userzone.stories.index') }}">My stories</a><a href="{{ route('userzone.trash.index') }}">Trash</a><a href="{{ route('profile.edit') }}">Account settings</a><a href="{{ route('authors.show', $user) }}">Public profile</a></div>
        <h2>Your latest stories</h2><div class="story-grid">@forelse($stories as $story)<x-story-card :story="$story" :manage="true" />@empty<p>Your first story starts here.</p>@endforelse</div>
    </section>
</x-site-layout>
