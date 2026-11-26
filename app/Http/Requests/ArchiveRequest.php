<?php

namespace App\Http\Requests;

use App\Geography;
use App\Models\Story;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArchiveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', Rule::in(array_keys(Story::CATEGORIES))],
            'tag' => ['nullable', 'integer', 'exists:tags,id'],
            'country' => ['nullable', 'string', Rule::in(array_keys(app(Geography::class)->countries()))],
            'sort' => ['nullable', 'string', Rule::in(['latest', 'score'])],
            'reset' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        $defaults = ['q' => '', 'category' => '', 'tag' => '', 'country' => '', 'sort' => 'latest'];
        $filters = $this->hasAny(array_keys($defaults))
            ? array_replace($defaults, $this->safe()->only(array_keys($defaults)))
            : array_replace($defaults, $this->session()->get('archive.filters', []));
        $this->session()->put('archive.filters', $filters);

        return $filters;
    }
}
