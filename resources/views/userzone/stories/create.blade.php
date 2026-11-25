<x-site-layout title="Tell your story">
    <section class="form-page">
        <a class="back-link" href="{{ route('stories.index') }}">← Back to the archive</a>
        <p class="eyebrow">Add to the archive</p>
        <h1>What happened there?</h1>
        <p class="intro">A place, a memory, and the part you still can't explain.</p>
        <form method="POST" action="{{ route('userzone.stories.store') }}">
            @csrf
            @include('userzone.stories.form')
            <p class="field-help">Your story will be public, with your name and its location.</p>
            <div class="form-actions"><button type="submit">Publish story ↗</button><a href="{{ route('stories.index') }}">Cancel</a></div>
        </form>
    </section>
</x-site-layout>
