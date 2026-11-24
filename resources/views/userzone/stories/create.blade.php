<x-site-layout title="Tell your story">
    <section class="form-page">
        <a class="back-link" href="{{ route('stories.index') }}">← Back to the archive</a>
        <p class="eyebrow">Add to the archive</p>
        <h1>What happened there?</h1>
        <p class="intro">A place, a memory, and the part you still can't explain.</p>
        <form method="POST" action="{{ route('userzone.stories.store') }}">
            @csrf
            <x-form-textinput name="title" label="Story title" :required="true" maxlength="255" autofocus />
            <div class="form-row">
                <div class="field">
                    <label for="location_id">Location</label>
                    <select name="location_id" id="location_id" required>
                        <option value="">Choose a place</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('location_id', request('location')) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')<p class="error">{{ $message }}</p>@enderror
                    <a class="field-help" href="{{ route('userzone.locations.create') }}">Place not listed? Add a location first.</a>
                </div>
                <div class="field">
                    <label for="category">Category</label>
                    <select name="category" id="category" required>
                        <option value="ghost" @selected(old('category') === 'ghost')>Ghost story</option>
                        <option value="urban_legend" @selected(old('category') === 'urban_legend')>Urban legend</option>
                        <option value="conspiracy" @selected(old('category') === 'conspiracy')>Conspiracy</option>
                    </select>
                    @error('category')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <x-form-textarea name="body" label="Your story" :required="true" minlength="20" maxlength="30000" placeholder="It started when…" />
            <p class="field-help">Your story will be public, with your name and its location.</p>
            <div class="form-actions"><button type="submit">Publish story ↗</button><a href="{{ route('stories.index') }}">Cancel</a></div>
        </form>
    </section>
</x-site-layout>
