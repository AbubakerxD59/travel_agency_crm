<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbbreviationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('abbreviations.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper(trim((string) $this->input('code'))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9\-\/]+$/',
                Rule::unique('abbreviations', 'code'),
            ],
            'full_form' => ['required', 'string', 'max:255'],
        ];
    }
}
