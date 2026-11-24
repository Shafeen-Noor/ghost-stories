@props(['name', 'label', 'value' => '', 'rows' => 10, 'required' => false])

<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" @required($required) {{ $attributes }} @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror>{{ old($name, $value) }}</textarea>
    @error($name)
        <p class="error" id="{{ $name }}-error">{{ $message }}</p>
    @enderror
</div>
