<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignLeadRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $total = $this->input('total_passengers');
        $this->merge([
            'total_passengers' => $total === '' || $total === null ? null : $total,
        ]);
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'agent_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (! User::role('agent')->whereKey($value)->exists()) {
                        $fail('The selected '.$attribute.' is invalid.');
                    }
                },
            ],
            'customer_name' => ['required', 'string', 'max:150'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'city' => ['nullable', 'string', 'max:120'],
            'total_passengers' => ['nullable', 'integer', 'min:1', 'max:500'],
            'source' => ['nullable', 'string', Rule::in(array_keys(getSources()))],
            'notes' => ['nullable', 'string'],
        ];
    }
}
