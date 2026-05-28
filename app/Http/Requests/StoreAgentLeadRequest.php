<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAgentLeadRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $total = $this->input('total_passengers');
        $this->merge([
            'total_passengers' => $total === '' || $total === null ? null : $total,
            'email' => $this->input('email') === '' ? null : $this->input('email'),
        ]);
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->can('leads.create');
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'city' => ['nullable', 'string', 'max:120'],
            'total_passengers' => ['nullable', 'integer', 'min:1', 'max:500'],
            'source' => ['nullable', 'string', Rule::in(array_keys(getAgentLeadSources()))],
            'notes' => ['nullable', 'string'],
        ];
    }
}
