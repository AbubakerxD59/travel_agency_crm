<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->website_link === '') {
            $this->merge(['website_link' => null]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can('companies.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'website_link' => ['nullable', 'string', 'url', 'max:255'],
        ];
    }
}
