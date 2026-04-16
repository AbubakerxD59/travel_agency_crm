<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('agents.manage') ?? false;
    }

    public function rules(): array
    {
        /** @var User $agent */
        $agent = $this->route('agent');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($agent->id)],
            'phone_number' => ['required', 'string', 'max:32'],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
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
        if (! $this->filled('password')) {
            $this->merge([
                'password' => null,
                'confirm_password' => null,
                'password_confirmation' => null,
            ]);
        } else {
            $this->merge([
                'password_confirmation' => $this->input('confirm_password'),
            ]);
        }
    }
}
