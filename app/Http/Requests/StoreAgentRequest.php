<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('agents.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'string', 'max:32'],
            'agent_cnic' => ['nullable', 'string', 'max:32'],
            'home_address' => ['nullable', 'string', 'max:1000'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone_number' => ['nullable', 'string', 'max:32'],
            'guardian_cnic' => ['nullable', 'string', 'max:32'],
            'role' => ['required', 'string', Rule::in(['agent'])],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone_number' => 'phone number',
            'agent_cnic' => 'agent cnic',
            'home_address' => 'home address',
            'guardian_name' => 'guardian name',
            'guardian_phone_number' => 'guardian phone number',
            'guardian_cnic' => 'guardian cnic',
            'confirm_password' => 'confirm password',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'password_confirmation' => $this->input('confirm_password'),
        ]);
    }
}
