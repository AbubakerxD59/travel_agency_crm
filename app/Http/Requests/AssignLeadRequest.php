<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignLeadRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('email') && $this->input('email') === '') {
            $this->merge(['email' => null]);
        }
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->hasRole('super-admin');
    }

    public function rules(): array
    {
        return [
            'agent_id' => [
                'required',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $agentQuery = User::role(User::ROLE_AGENT)->whereKey($value);

                    if ($this->filled('company_id')) {
                        $agentQuery->where('company_id', $this->integer('company_id'));
                    }

                    if (! $agentQuery->exists()) {
                        $fail($this->filled('company_id')
                            ? __('The selected agent does not belong to the selected company.')
                            : __('The selected agent is invalid.'));
                    }
                },
            ],
            'customer_name' => ['required', 'string', 'max:150'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'city' => ['required', 'string', 'max:120'],
            'total_passengers' => ['required', 'integer', 'min:1', 'max:500'],
            'source' => ['required', 'string', Rule::in(array_keys(getSources()))],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'agent_id' => 'agent',
            'customer_name' => 'customer name',
            'phone_number' => 'phone number',
            'company_id' => 'company',
            'total_passengers' => 'total passengers',
        ];
    }
}
