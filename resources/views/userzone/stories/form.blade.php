            <x-form-textinput name="title" label="Story title" :required="true" :value="$story->title ?? ''" maxlength="255" autofocus />
            <div class="form-row">
                <div class="field">
                    <label for="location_id">Location</label>
                    <select name="location_id" id="location_id" required>
                        <option value="">Choose a place</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected(old('location_id', $story->location_id ?? request('location')) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')<p class="error">{{ $message }}</p>@enderror
                    <a class="field-help" href="{{ route('userzone.locations.create') }}">Place not listed? Add a location first.</a>
                </div>
                <div class="field">
                    <label for="category">Category</label>
                    <select name="category" id="category" required>
                        <option value="ghost" @selected(old('category', $story->category ?? 'ghost') === 'ghost')>Ghost story</option>
                        <option value="urban_legend" @selected(old('category', $story->category ?? 'ghost') === 'urban_legend')>Urban legend</option>
                        <option value="conspiracy" @selected(old('category', $story->category ?? 'ghost') === 'conspiracy')>Conspiracy</option>
                    </select>
                    @error('category')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>
            <x-form-textarea name="body" label="Your story" :value="$story->body ?? ''" :required="true" minlength="20" maxlength="30000" placeholder="It started when…" />
