<?php

namespace App\Http\Requests;

use App\Geography;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'tags' => ['nullable', 'array', 'max:5'],
            'tags.*' => ['integer', 'distinct', 'exists:tags,id'],
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'body' => ['required', 'string', 'min:20', 'max:30000'],
            'category' => ['required', Rule::in(['ghost', 'urban_legend', 'conspiracy'])],
            'location_id' => ['nullable', 'required_without:country_code', Rule::prohibitedIf($this->filled('country_code')), 'exists:locations,id'],
            'country_code' => ['nullable', 'required_without:location_id', 'string', Rule::in(array_keys(app(Geography::class)->countries()))],
            'region_code' => ['nullable', 'string', Rule::prohibitedIf($this->filled('location_id')), Rule::in(array_keys($regions))],
        ];
    }
}
