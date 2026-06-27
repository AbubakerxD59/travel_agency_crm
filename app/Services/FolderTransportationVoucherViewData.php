<?php

namespace App\Services;

use App\Models\Folder;
use App\Support\AbbreviationResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FolderTransportationVoucherViewData
{
    public function __construct(
        private readonly AbbreviationResolver $abbreviations,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Folder $folder, bool $forPdf = false): array
    {
        $folder->load([
            'agent',
            'company',
            'passengers',
            'transportDetails',
            'itineraries',
        ]);

        $companyConfig = config('invoice.company');
        $leadPassenger = $folder->passengers->first();

        $transportDetails = $this->filterModelsWithData($folder->transportDetails, [
            'origin', 'destination', 'service_date', 'pickup_time', 'vehicle_type', 'description',
        ]);
        $itineraries = $this->filterModelsWithData($folder->itineraries, [
            'airline_code', 'airline_number', 'departure_date', 'departure_time',
            'departure_airport', 'arrival_airport', 'arrival_time', 'arrival_date',
        ]);

        return [
            'booking_date' => $this->formatBookingDate($folder->booking_date ?? $folder->created_at),
            'agent_email' => trim((string) ($folder->agent?->email ?? '')),
            'direct_line' => trim((string) ($folder->agent?->direct_line ?? '')) ?: '—',
            'company' => [
                'name' => $folder->company?->name ?? $companyConfig['name'],
                'website' => $folder->company?->website_link ?? $companyConfig['website'],
                'logo_url' => $folder->company?->imageUrl() ?? $companyConfig['logo_url'],
            ],
            'internal_ref_no' => (string) $folder->id,
            'voucher_number' => $folder->vendor_reference ?: (string) $folder->id,
            'lead_guest_name' => $this->leadGuestName($folder, $leadPassenger),
            'pax_mobile' => trim((string) ($leadPassenger?->phone ?? '')) ?: '—',
            'transport_company_mobile' => '+966000000000',
            'pax_summary' => $this->paxSummary($folder),
            'transport' => $transportDetails->map(fn ($transport) => [
                'from' => trim((string) ($transport->origin ?? '')),
                'to' => trim((string) ($transport->destination ?? '')),
                'pickup_time' => format_invoice_time($transport->pickup_time),
                'pickup_date' => $this->formatTransportDate($transport->service_date),
                'vehicle' => trim((string) ($transport->vehicle_type ?? '')),
            ])->all(),
            'flight_itinerary' => $itineraries->map(fn ($leg) => [
                'operated_by' => $this->abbreviations->display($leg->airline_code ?? ''),
                'flight_no' => trim((string) ($leg->airline_number ?? '')),
                'departure_date' => $this->formatFlightDate($leg->departure_date, $forPdf),
                'departure_time' => format_invoice_time($leg->departure_time),
                'from' => $this->abbreviations->display($leg->departure_airport ?? ''),
                'arrival_time' => format_invoice_time($leg->arrival_time),
                'to' => $this->abbreviations->display($leg->arrival_airport ?? ''),
                'arrival_date' => $this->formatFlightDate($leg->arrival_date, $forPdf),
            ])->all(),
        ];
    }

    private function formatBookingDate(mixed $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return Carbon::parse($date)->format('F d, Y');
    }

    private function formatTransportDate(mixed $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return Carbon::parse($date)->format('d-M-y');
    }

    private function formatFlightDate(mixed $date, bool $compact = false): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        return Carbon::parse($date)->format($compact ? 'd-M-y' : 'j F, Y');
    }

    private function leadGuestName(Folder $folder, mixed $leadPassenger): string
    {
        if ($leadPassenger !== null) {
            $name = trim(implode(' ', array_filter([
                $leadPassenger->first_name ?? '',
                $leadPassenger->middle_name ?? '',
                $leadPassenger->last_name ?? '',
            ])));

            if ($name !== '') {
                return $name;
            }
        }

        return trim((string) ($folder->customer_name ?? '')) ?: '—';
    }

    private function paxSummary(Folder $folder): string
    {
        $counts = [
            'Adult' => 0,
            'Child' => 0,
            'Youth' => 0,
            'Infant' => 0,
        ];

        foreach ($folder->passengers as $passenger) {
            $type = (string) ($passenger->passenger_type ?? 'Adult');

            if (array_key_exists($type, $counts)) {
                $counts[$type]++;
            }
        }

        return sprintf(
            '%d Adults + %d Children + %d Youth + %d Infants',
            $counts['Adult'],
            $counts['Child'],
            $counts['Youth'],
            $counts['Infant'],
        );
    }

    /**
     * @param  iterable<int, object>  $models
     * @param  list<string>  $fields
     * @return Collection<int, object>
     */
    private function filterModelsWithData(iterable $models, array $fields): Collection
    {
        return collect($models)
            ->filter(fn (object $model): bool => $this->modelHasData($model, $fields))
            ->values();
    }

    /**
     * @param  list<string>  $fields
     */
    private function modelHasData(object $model, array $fields): bool
    {
        foreach ($fields as $field) {
            $value = $model->{$field} ?? null;

            if ($value === null) {
                continue;
            }

            if (is_string($value) && trim($value) !== '') {
                return true;
            }

            if (is_numeric($value)) {
                return true;
            }
        }

        return false;
    }
}
