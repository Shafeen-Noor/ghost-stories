<?php

namespace App\Http\Requests;

use App\Geography;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $country = $this->input('country_code');
        $regions = is_string($country) ? app(Geography::class)->regions($country) : [];

        return [
            'country_code' => ['required', 'string', Rule::in(array_keys(app(Geography::class)->countries()))],
            'region_code' => ['nullable', 'string', Rule::in(array_keys($regions))],
        ];
    }
}
