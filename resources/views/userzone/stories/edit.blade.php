<x-site-layout title="Edit your story">
    <section class="writer-page">
        <a class="back-link" href="{{ route('userzone.stories.index') }}">← My stories</a>
        <p class="eyebrow">Your story, in your words</p>
        <h1>Make it even better.</h1>
        <form class="writer-form" enctype="multipart/form-data" method="POST" action="{{ route('userzone.stories.update', $story) }}">
            @csrf
            @method('PATCH')
            @include('userzone.stories.form')
            <p class="field-help">Your story's link stays the same when you change the title.</p>
            <div class="form-actions publish-actions"><button type="submit">Save changes</button><a href="{{ route('userzone.stories.index') }}">Cancel</a></div>
        </form>
    </section>
</x-site-layout>
