<x-form-textinput name="title" label="Story title" :required="true" :value="$story->title ?? ''" maxlength="255" autofocus />
<x-location-picker :geography="$geography" :locations="$locations" :location="$story->location ?? null" />
    <div class="field">
        <label for="category">Category</label>
        <select name="category" id="category" required>
            <option value="ghost" @selected(old('category', $story->category ?? 'ghost') === 'ghost')>Ghost story</option>
            <option value="urban_legend" @selected(old('category', $story->category ?? 'ghost') === 'urban_legend')>Urban legend</option>
            <option value="conspiracy" @selected(old('category', $story->category ?? 'ghost') === 'conspiracy')>Conspiracy</option>
        </select>
        @error('category')<p class="error">{{ $message }}</p>@enderror
    </div>
<x-form-textarea name="body" label="Your story" :value="$story->body ?? ''" :required="true" minlength="20" maxlength="30000" placeholder="It started when…" />

@php($selectedTags = old('tags', isset($story) ? $story->tags->modelKeys() : []))
<fieldset class="tag-choices"><legend>Tags (up to five)</legend>
    @foreach($tags as $tag)
        <label><input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, is_array($selectedTags) ? $selectedTags : []))> {{ $tag->name }}</label>
    @endforeach
    @error('tags')<p class="error">{{ $message }}</p>@enderror
    @error('tags.*')<p class="error">{{ $message }}</p>@enderror
</fieldset>
