<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckLeadDuplicateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->input('email') === '' ? null : $this->input('email'),
            'exclude_lead_id' => $this->input('exclude_lead_id') === '' || $this->input('exclude_lead_id') === null
                ? null
                : $this->input('exclude_lead_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'exclude_lead_id' => ['nullable', 'integer', 'exists:leads,id'],
        ];
    }
}
