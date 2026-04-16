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
            'role' => ['required', 'string', Rule::in(['agent'])],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'phone_number' => 'phone number',
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
