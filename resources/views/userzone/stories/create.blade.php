<x-site-layout title="Tell your story">
    <section class="writer-page">
        <a class="back-link" href="{{ route('stories.index') }}">← Back to the archive</a>
        <p class="eyebrow">Add to the archive</p>
        <h1>Make it a story.</h1>
        <p class="intro">Your words. Your perspective. A story someone will remember.</p>
        <form class="writer-form" enctype="multipart/form-data" method="POST" action="{{ route('userzone.stories.store') }}">
            @csrf
            @include('userzone.stories.form')
            <p class="field-help">Your story will be public, with your name and its location.</p>
            <div class="form-actions publish-actions"><button type="submit">Publish story ↗</button><a href="{{ route('stories.index') }}">Cancel</a></div>
        </form>
    </section>
</x-site-layout>
