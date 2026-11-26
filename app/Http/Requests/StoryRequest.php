<?php

namespace App\Http\Requests;

use App\Geography;
use App\Models\Story;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $story = $this->route('story');

        return $story ? $this->user()->can('update', $story) : $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $country = $this->input('country_code');
        $regions = is_string($country) ? app(Geography::class)->regions($country) : [];

        return [
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-85,85'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'cover_preset' => ['nullable', 'string', Rule::in(['auto', 'house', 'station', 'coast'])],
            'backdrop' => ['nullable', 'string', Rule::in(['none', 'cover', 'house', 'station', 'coast'])],
            'reading_theme' => ['nullable', 'string', Rule::in(['light', 'dark', 'sepia'])],
            'cover_layout' => ['nullable', 'string', Rule::in(['split', 'full'])],
            'cover_position' => ['nullable', 'string', Rule::in(['top', 'center', 'bottom'])],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=8000,max_height=8000'],
            'background_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=8000,max_height=8000'],
            'gallery' => ['nullable', 'array', 'max:6'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=8000,max_height=8000'],
            'remove_cover' => ['nullable', 'boolean'],
            'remove_background' => ['nullable', 'boolean'],
            'remove_gallery' => ['nullable', 'array', 'max:6'],
            'remove_gallery.*' => ['integer', 'distinct', 'min:0', 'max:5'],
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'body' => ['required', 'string', 'min:20', 'max:30000'],
            'category' => ['required', 'string', Rule::in(array_keys(Story::CATEGORIES))],
            'location_id' => ['nullable', 'integer', 'required_without:country_code', Rule::prohibitedIf($this->filled('country_code')), 'exists:locations,id'],
            'country_code' => ['nullable', 'required_without:location_id', 'string', Rule::in(array_keys(app(Geography::class)->countries()))],
            'region_code' => ['nullable', 'string', Rule::prohibitedIf($this->filled('location_id')), Rule::in(array_keys($regions))],
        ];
    }

    /** @return array<\Closure> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $existing = $this->route('story')?->gallery_paths ?? [];
            $remaining = array_diff_key($existing, array_flip((array) $this->input('remove_gallery', [])));
            if (count($remaining) + count($this->file('gallery', [])) > 6) {
                $validator->errors()->add('gallery', 'Keep up to six gallery images. Remove an existing image before adding another.');
            }
        }];
    }
}
