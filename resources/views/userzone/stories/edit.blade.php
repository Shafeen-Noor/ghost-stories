<x-site-layout title="Edit your story">
    <section class="form-page">
        <a class="back-link" href="{{ route('userzone.stories.index') }}">← My stories</a>
        <p class="eyebrow">Your story, in your words</p>
        <h1>Edit your story</h1>
        <form method="POST" action="{{ route('userzone.stories.update', $story) }}">
            @csrf
            @method('PATCH')
            @include('userzone.stories.form')
            <p class="field-help">Your story's link stays the same when you change the title.</p>
            <div class="form-actions"><button type="submit">Save changes</button><a href="{{ route('userzone.stories.index') }}">Cancel</a></div>
        </form>
    </section>
</x-site-layout>
