<?php

namespace App\Http\Requests;

use App\Models\Abbreviation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAbbreviationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('abbreviations.manage') ?? false;
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
        /** @var Abbreviation $abbreviation */
        $abbreviation = $this->route('abbreviation');

        return [
            'code' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9\-\/]+$/',
                Rule::unique('abbreviations', 'code')->ignore($abbreviation->id),
            ],
            'full_form' => ['required', 'string', 'max:255'],
        ];
    }
}
