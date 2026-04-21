<?php

namespace App\Http\Requests;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
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

                    if (!User::role('agent')->whereKey($value)->exists()) {
                        $fail('The selected '.$attribute.' is invalid.');
                    }
                },
            ],
            'order_type' => ['required', 'string', 'max:120'],
            'vendor_reference' => ['nullable', 'string', 'max:255'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'status' => ['required', 'string', Rule::in(Lead::statusKeys())],
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'travel_date' => ['required', 'date'],
            'balance_due_date' => ['nullable', 'date'],
            'flight_itinerary' => ['nullable', 'string'],
            'itineraries' => ['required', 'array', 'min:1'],
            'itineraries.*.sr_no' => ['nullable', 'integer', 'min:1'],
            'itineraries.*.airline_code' => ['nullable', 'string', 'max:20'],
            'itineraries.*.airline_number' => ['nullable', 'string', 'max:30'],
            'itineraries.*.class' => ['nullable', 'string', 'max:20'],
            'itineraries.*.departure_date' => ['nullable', 'date'],
            'itineraries.*.departure_airport' => ['nullable', 'string', 'max:30'],
            'itineraries.*.arrival_airport' => ['nullable', 'string', 'max:30'],
            'itineraries.*.departure_time' => ['nullable', 'date_format:H:i'],
            'itineraries.*.arrival_time' => ['nullable', 'date_format:H:i'],
            'itineraries.*.arrival_date' => ['nullable', 'date'],
            'passengers' => ['required', 'array', 'min:1'],
            'passengers.*.title' => ['nullable', 'string', 'max:20'],
            'passengers.*.first_name' => ['nullable', 'string', 'max:100'],
            'passengers.*.middle_name' => ['nullable', 'string', 'max:100'],
            'passengers.*.last_name' => ['nullable', 'string', 'max:100'],
            'passengers.*.passenger_type' => ['nullable', 'string', 'max:30'],
            'passengers.*.email' => ['nullable', 'email', 'max:255'],
            'passengers.*.phone' => ['nullable', 'string', 'max:30'],
            'passengers.*.date_of_birth' => ['nullable', 'date'],
            'passengers.*.passport_details' => ['nullable', 'string', 'max:255'],
            'package_costs' => ['required', 'array', 'min:1'],
            'package_costs.*.ticket_no' => ['nullable', 'string', 'max:50'],
            'package_costs.*.ticket_date' => ['nullable', 'date'],
            'package_costs.*.airline_from' => ['nullable', 'string', 'max:30'],
            'package_costs.*.airline_to' => ['nullable', 'string', 'max:30'],
            'package_costs.*.fare' => ['nullable', 'numeric', 'min:0'],
            'package_costs.*.tax' => ['nullable', 'numeric', 'min:0'],
            'package_costs.*.total_cost' => ['nullable', 'numeric', 'min:0'],
            'package_costs.*.margin' => ['nullable', 'numeric', 'min:0'],
            'package_costs.*.sell' => ['nullable', 'numeric', 'min:0'],
            'package_costs.*.supplier' => ['nullable', 'string', 'max:100'],
            'package_costs.*.pnr' => ['nullable', 'string', 'max:50'],
            'ziarat_makkah' => ['sometimes', 'boolean'],
            'ziarat_madinah' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $agentId = $this->input('agent_id');
        $balanceDue = $this->input('balance_due_date');
        $itineraries = collect($this->input('itineraries', []))
            ->filter(function ($row) {
                if (!is_array($row)) {
                    return false;
                }

                foreach ($row as $value) {
                    if (is_string($value) && trim($value) !== '') {
                        return true;
                    }

                    if (!is_string($value) && $value !== null && $value !== '') {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();
        $passengers = collect($this->input('passengers', []))
            ->filter(function ($row) {
                if (!is_array($row)) {
                    return false;
                }

                foreach ($row as $value) {
                    if (is_string($value) && trim($value) !== '') {
                        return true;
                    }

                    if (!is_string($value) && $value !== null && $value !== '') {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();
        $packageCosts = collect($this->input('package_costs', []))
            ->filter(function ($row) {
                if (!is_array($row)) {
                    return false;
                }

                foreach ($row as $value) {
                    if (is_string($value) && trim($value) !== '') {
                        return true;
                    }

                    if (!is_string($value) && $value !== null && $value !== '') {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();

        $this->merge([
            'agent_id' => $agentId === '' || $agentId === null ? null : $agentId,
            'balance_due_date' => $balanceDue === '' || $balanceDue === null ? null : $balanceDue,
            'itineraries' => $itineraries,
            'passengers' => $passengers,
            'package_costs' => $packageCosts,
            'ziarat_makkah' => $this->boolean('ziarat_makkah'),
            'ziarat_madinah' => $this->boolean('ziarat_madinah'),
        ]);
    }

    public function attributes(): array
    {
        return [
            'order_type' => 'order type',
            'vendor_reference' => 'vendor reference',
            'company_id' => 'company',
            'destination_id' => 'destination',
            'travel_date' => 'travel date',
            'balance_due_date' => 'balance due date',
            'flight_itinerary' => 'flight itinerary',
            'itineraries' => 'itineraries',
            'passengers' => 'passengers',
            'package_costs' => 'hotel/package costs',
            'ziarat_makkah' => 'Ziarat Makkah',
            'ziarat_madinah' => 'Ziarat Madinah',
        ];
    }
}
