<?php

namespace App\Services;

use App\Models\Lead;

class LeadDuplicateChecker
{
    public function find(?string $email, string $phoneNumber, ?int $excludeLeadId = null): ?Lead
    {
        $phone = trim($phoneNumber);
        if ($phone === '') {
            return null;
        }

        $normalizedEmail = $this->normalizeEmail($email);

        $query = Lead::query()
            ->with(['agent'])
            ->where('phone_number', $phone);

        if ($normalizedEmail === null) {
            $query->where(function ($builder): void {
                $builder
                    ->whereNull('email')
                    ->orWhere('email', '');
            });
        } else {
            $query->whereRaw('LOWER(TRIM(email)) = ?', [$normalizedEmail]);
        }

        if ($excludeLeadId !== null) {
            $query->whereKeyNot($excludeLeadId);
        }

        return $query->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(Lead $lead, string $showRouteName): array
    {
        return [
            'id' => $lead->id,
            'customer_name' => $lead->customer_name,
            'phone_number' => $lead->phone_number,
            'email' => $lead->email,
            'status_label' => $lead->statusLabel(),
            'agent_name' => $lead->agent_name ?? $lead->agent?->name,
            'created_at' => $lead->created_at?->format('M j, Y'),
            'show_url' => route($showRouteName, $lead),
        ];
    }

    private function normalizeEmail(?string $email): ?string
    {
        if ($email === null) {
            return null;
        }

        $normalized = strtolower(trim($email));

        return $normalized === '' ? null : $normalized;
    }
}
