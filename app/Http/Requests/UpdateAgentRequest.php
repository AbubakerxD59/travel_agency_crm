<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesTeamMemberManager;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAgentRequest extends FormRequest
{
    use ValidatesTeamMemberManager;
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($agent->id)->whereNull('deleted_at'),
            ],
            'phone_number' => ['required', 'string', 'max:32'],
            'direct_line' => ['nullable', 'string', 'max:32'],
            'agent_cnic' => ['nullable', 'string', 'max:32'],
            'agent_cnic_photo' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'home_address' => ['nullable', 'string', 'max:1000'],
            'guardian_name' => ['nullable', 'string', 'max:255'],
            'guardian_phone_number' => ['nullable', 'string', 'max:32'],
            'guardian_cnic' => ['nullable', 'string', 'max:32'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'role' => ['required', 'string', Rule::in(['agent', 'manager'])],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
            ...$this->teamMemberManagerRules($agent->id),
        ];
    }

    public function attributes(): array
    {
        return [
            'phone_number' => 'phone number',
            'direct_line' => 'direct line',
            'agent_cnic' => 'agent cnic',
            'agent_cnic_photo' => 'agent cnic photo',
            'home_address' => 'home address',
            'guardian_name' => 'guardian name',
            'guardian_phone_number' => 'guardian phone number',
            'guardian_cnic' => 'guardian cnic',
            'company_id' => 'company',
            'manager_id' => 'manager',
            'confirm_password' => 'confirm password',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->clearManagerIdUnlessAgentRole();

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
