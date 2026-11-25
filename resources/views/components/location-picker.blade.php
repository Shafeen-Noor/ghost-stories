@props(['geography', 'locations' => null, 'location' => null])

@php
    $country = old('country_code', $location?->country_code);
    $region = old('region_code', $location?->region_code);
    $savedLocation = old('location_id', $location && !$location->country_code ? $location->id : request('location'));
    $regions = is_string($country) ? $geography->regions($country) : [];
@endphp
<fieldset class="location-picker" data-region-url="{{ route('geography.regions', ['country' => '__COUNTRY__']) }}">
    <legend>Where did it happen?</legend>
    <p class="field-help">Choose a country, then a region if you know it. No need to add a place first.</p>
    <div class="form-row">
        <div class="field">
            <label for="country_code">Country</label>
            <select name="country_code" id="country_code" autocomplete="country" @error('country_code') aria-invalid="true" @enderror>
                <option value="">Choose a country</option>
                @foreach($geography->countries() as $code => $name)
                    <option value="{{ $code }}" @selected($country === $code)>{{ $name }}</option>
                @endforeach
            </select>
            @error('country_code')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="region_code">Region / state / province (optional)</label>
            <select name="region_code" id="region_code" @disabled(!$regions) @error('region_code') aria-invalid="true" @enderror>
                <option value="">Country only</option>
                @foreach($regions as $code => $name)
                    <option value="{{ $code }}" @selected($region === $code)>{{ $name }}</option>
                @endforeach
            </select>
            @error('region_code')<p class="error">{{ $message }}</p>@enderror
        </div>
    </div>
    <p class="field-help" data-region-status role="status"></p>
    @if($locations !== null)
        <details @if($savedLocation) open @endif>
            <summary>Or use an existing place</summary>
            <div class="field">
                <label for="location_id">Saved place</label>
                <select name="location_id" id="location_id">
                    <option value="">Choose an existing place instead</option>
                    @foreach($locations as $place)
                        <option value="{{ $place->id }}" @selected($savedLocation == $place->id)>{{ $place->name }}</option>
                    @endforeach
                </select>
                @error('location_id')<p class="error">{{ $message }}</p>@enderror
            </div>
        </details>
    @endif
    <noscript><p class="field-help">You can save a country without choosing a region. Enable JavaScript to load regions when you change countries.</p></noscript>
</fieldset>
@once
    <script src="{{ asset('js/location-picker.js') }}" defer></script>
@endonce
