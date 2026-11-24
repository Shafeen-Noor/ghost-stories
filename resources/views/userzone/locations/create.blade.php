<x-site-layout title="Add a location">
    <section class="form-page">
        <a class="back-link" href="{{ route('userzone.stories.create') }}">← Back to your story</a>
        <p class="eyebrow">Put a place in the archive</p>
        <h1>Where did it happen?</h1>
        <form method="POST" action="{{ route('userzone.locations.store') }}">
            @csrf
            <x-form-textinput name="name" label="Place name" :required="true" maxlength="255" autofocus />
            <x-form-textarea name="description" label="A little about the place (optional)" :rows="4" maxlength="2000" />
            <button type="submit">Add location →</button>
        </form>
    </section>
</x-site-layout>
