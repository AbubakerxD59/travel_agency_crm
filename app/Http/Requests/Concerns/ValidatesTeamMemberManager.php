<?php

namespace App\Http\Requests\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ValidatesTeamMemberManager
{
    /**
     * @return array<string, array<int, mixed>>
     */
    protected function teamMemberManagerRules(?int $excludeUserId = null): array
    {
        return [
            'manager_id' => [
                'nullable',
                'integer',
                Rule::prohibitedIf(fn () => $this->input('role') !== User::ROLE_AGENT),
                function (string $attribute, mixed $value, \Closure $fail) use ($excludeUserId): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if ($excludeUserId !== null && (int) $value === $excludeUserId) {
                        $fail(__('A user cannot be their own manager.'));

                        return;
                    }

                    if (! User::role(User::ROLE_MANAGER)->visibleToStaff($this->user())->whereKey($value)->exists()) {
                        $fail(__('The selected manager is invalid.'));
                    }
                },
            ],
        ];
    }

    protected function clearManagerIdUnlessAgentRole(): void
    {
        if ($this->input('role') !== User::ROLE_AGENT) {
            $this->merge(['manager_id' => null]);
        }
    }
}
