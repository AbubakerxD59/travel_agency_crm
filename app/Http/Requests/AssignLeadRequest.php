<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignLeadRequest extends FormRequest
{
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
            'source' => ['nullable', 'string', Rule::in(['meta', 'google', 'whatsapp', 'referral'])],
            'notes' => ['nullable', 'string'],
        ];
    }
}
