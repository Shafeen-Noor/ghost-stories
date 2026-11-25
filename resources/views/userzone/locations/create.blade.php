<x-site-layout title="Choose a location">
    <section class="form-page">
        <a class="back-link" href="{{ route('userzone.stories.create') }}">← Back to your story</a>
        <p class="eyebrow">A place for your story</p>
        <h1>Choose a location</h1>
        <form method="POST" action="{{ route('userzone.locations.store') }}">
            @csrf
            <x-location-picker :geography="$geography" />
            <div class="form-actions"><button type="submit">Use this location</button></div>
        </form>
    </section>
</x-site-layout>
