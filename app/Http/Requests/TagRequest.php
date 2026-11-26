<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $name = trim($this->input('name'));
            $this->merge(['name' => $name, 'slug' => (string) str($name)->slug()]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:40', Rule::unique('tags')->ignore($this->route('tag'))],
            'slug' => ['required', 'string', 'max:80', Rule::unique('tags')->ignore($this->route('tag'))],
        ];
    }
}
