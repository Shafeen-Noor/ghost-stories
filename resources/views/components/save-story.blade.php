@props(['story'])
<details class="save-story" @if($errors->has('collection_id') || $errors->has('name')) open @endif><summary>Save to a collection +</summary>
@auth
<form method="POST" action="{{ route('collections.save', $story) }}">@csrf
<label for="save-collection">Choose a collection</label><select id="save-collection" name="collection_id"><option value="">Create a new collection below</option>@foreach(auth()->user()->readingCollections()->orderBy('name')->get() as $savedCollection)<option value="{{ $savedCollection->id }}" @selected(old('collection_id') == $savedCollection->id)>{{ $savedCollection->name }}</option>@endforeach</select>
<label for="collection-name">Or name a new collection</label><input id="collection-name" name="name" value="{{ is_string(old('name')) ? old('name') : '' }}" maxlength="80" placeholder="Legends to investigate">@error('collection_id')<p class="error">{{ $message }}</p>@enderror @error('name')<p class="error">{{ $message }}</p>@enderror<button>Save story</button><a href="{{ route('collections.index') }}">My collections ↗</a>
</form>
@else<p><a href="{{ route('login') }}">Log in</a> to keep your own private reading lists.</p>@endauth
</details>
