<x-site-layout title="Investigation desk">
    <section class="archive">
        <h1>Reading activity</h1><p></p>
        <p>{{ $published }} public {{ Str::plural('story', $published) }} · {{ $trashed }} {{ Str::plural('story', $trashed) }} in the trash</p>
        <div class="form-actions"><a class="button" href="{{ route('userzone.stories.create') }}">Tell a story</a><a href="{{ route('userzone.stories.index') }}">My stories</a><a href="{{ route('userzone.trash.index') }}">Trash</a><a href="{{ route('profile.edit') }}">Account settings</a><a href="{{ route('authors.show', $user) }}">Public profile</a></div>
        <section class="desk-cases"><div class="section-heading"><div><h2>Saved stories</h2></div><a href="{{ route('collections.index') }}">My collections ↗</a></div><p class="field-help">New comments since your last visit.</p><div class="trail-grid">@forelse($investigations as $case)<a href="{{ route('cases.show', $case) }}"><span class="eyebrow">{{ $case->location->name }}</span><h3>{{ $case->title }}</h3><span @class(['desk-new' => $case->new_comments > 0])>{{ $case->new_comments ? $case->new_comments.' new '.Str::plural('comment', $case->new_comments) : 'Caught up for now' }}</span><p>Read story</p></a>@empty<p>Your desk is clear. Save a story to a collection to follow it here. <a href="{{ route('cases.tonight') }}">Start with tonight's mystery ↗</a></p>@endforelse</div></section>
        <h2>Your latest stories</h2><div class="story-grid">@forelse($stories as $story)<x-story-card :story="$story" :manage="true" />@empty<p>Your first story starts here.</p>@endforelse</div>
    </section>
</x-site-layout>
