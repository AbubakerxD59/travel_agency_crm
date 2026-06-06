<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\FolderHotelDetail;
use App\Models\FolderPayment;

class FolderInvoiceViewData
{
    /**
     * @return array<string, mixed>
     */
    public function build(Folder $folder): array
    {
        $folder->load([
            'agent',
            'company',
            'destination',
            'passengers',
            'packageCosts',
            'hotelDetails',
            'transportDetails',
            'visaDetails',
            'otherDetails',
            'itineraries',
            'payments',
        ]);

        $summary = $folder->costSummary();
        $passengerCount = max($folder->passengers->count(), 1);
        $perPassengerSell = (int) round($summary['total_sale'] / $passengerCount);
        $firstCost = $folder->packageCosts->first();
        $flightDetails = $firstCost
            ? trim(($firstCost->airline_from ?? '').' to '.($firstCost->airline_to ?? ''))
            : '';

        $amountPaid = (float) $folder->payments
            ->where('approval_status', 'approved')
            ->sum(fn ($payment) => (float) ($payment->amount ?? 0));

        $invoiceTotal = (int) round($summary['total_sale']);
        $amountDue = max($invoiceTotal - (int) round($amountPaid), 0);

        $companyConfig = config('invoice.company');

        $hotelDetails = $this->filterModelsWithInvoiceData($folder->hotelDetails, [
            'sr_no', 'supplier', 'hotel_name', 'guest_name', 'rooms', 'type', 'meals',
            'date_in', 'date_out', 'nights', 'supplier_ref', 'status', 'cost', 'margin', 'sell', 'hotel_city',
        ]);
        $itineraries = $this->filterModelsWithInvoiceData($folder->itineraries, [
            'sr_no', 'airline_code', 'airline_number', 'class', 'departure_date',
            'departure_airport', 'arrival_airport', 'departure_time', 'arrival_time', 'arrival_date',
        ]);
        $transportDetails = $this->filterModelsWithInvoiceData($folder->transportDetails, [
            'supplier', 'description', 'origin', 'destination', 'service_date',
            'pickup_time', 'vehicle_type', 'cost', 'margin', 'sell', 'sar',
        ]);

        return [
            'direct_line' => trim((string) ($folder->agent?->direct_line ?? '')) ?: '—',
            'company' => [
                'name' => $folder->company?->name ?? $companyConfig['name'],
                'email' => $companyConfig['email'],
                'phone' => $companyConfig['phone'],
                'website' => $folder->company?->website_link ?? $companyConfig['website'],
                'logo_url' => $folder->company?->imageUrl() ?? $companyConfig['logo_url'],
            ],
            'booking_date' => format_invoice_date($folder->booking_date ?? $folder->created_at ?? now()),
            'invoice_number' => $folder->vendor_reference ?: (string) $folder->id,
            'agent_name' => folder_agent_display_name($folder),
            'travel_date' => format_invoice_date($folder->travel_date),
            'destination' => $folder->destination?->name ?? '—',
            'passenger_count' => $folder->passengers->count(),
            'passengers' => $folder->passengers->map(fn ($passenger) => [
                'title' => $passenger->title ?? '',
                'first_name' => strtoupper($passenger->first_name ?? ''),
                'middle_name' => strtoupper($passenger->middle_name ?? ''),
                'last_name' => strtoupper($passenger->last_name ?? ''),
                'flight_details' => $flightDetails,
                'type' => $passenger->passenger_type ?? 'Adult',
                'price' => $perPassengerSell,
            ])->all(),
            'invoice_total' => $invoiceTotal,
            'amount_due' => $amountDue,
            'approved_payments' => $folder->payments
                ->where('approval_status', FolderPayment::STATUS_APPROVED)
                ->sortBy([
                    ['payment_date', 'asc'],
                    ['id', 'asc'],
                ])
                ->values()
                ->map(fn ($payment) => [
                    'amount_formatted' => '£ '.number_format((float) ($payment->amount ?? 0), 0),
                    'payment_date' => format_invoice_date($payment->payment_date),
                ])
                ->all(),
            'balance_due_date' => format_invoice_date($folder->balance_due_date),
            'hotels' => $hotelDetails->map(fn (FolderHotelDetail $hotel) => [
                'name' => $hotel->hotel_name ?? '—',
                'city' => $hotel->hotel_city ?? '—',
                'nights' => $hotel->nights !== null ? str_pad((string) $hotel->nights, 2, '0', STR_PAD_LEFT) : '—',
                'rooms' => $hotel->rooms ?? '—',
                'type' => $hotel->type ?? '—',
            ])->all(),
            'flight_itinerary' => $itineraries->map(fn ($leg) => [
                'operated_by' => $leg->airline_code ?? '',
                'flight_no' => $leg->airline_number ?? '',
                'departure_date' => format_invoice_date($leg->departure_date),
                'departure_time' => format_invoice_time($leg->departure_time),
                'from' => $leg->departure_airport ?? '',
                'arrival_time' => format_invoice_time($leg->arrival_time),
                'to' => $leg->arrival_airport ?? '',
                'arrival_date' => format_invoice_date($leg->arrival_date),
            ])->all(),
            'hotel_itinerary' => $this->buildHotelItinerary($hotelDetails),
            'visa_details' => $folder->visaDetails
                ->filter(fn ($visa) => trim((string) ($visa->description ?? '')) !== ''
                    || trim((string) ($visa->supplier ?? '')) !== '')
                ->map(fn ($visa) => [
                    'supplier' => $visa->supplier ?? '',
                    'description' => $visa->description ?? '',
                ])
                ->values()
                ->all(),
            'other_details' => $folder->otherDetails
                ->filter(fn ($other) => trim((string) ($other->description ?? '')) !== ''
                    || trim((string) ($other->supplier ?? '')) !== '')
                ->map(fn ($other) => [
                    'supplier' => $other->supplier ?? '',
                    'description' => $other->description ?? '',
                ])
                ->values()
                ->all(),
            'transport' => $transportDetails->map(function ($transport) use ($folder) {
                $leadingPassenger = $folder->passengers->first();

                return [
                    'description' => $transport->description ?? '',
                    'leading_passenger' => $leadingPassenger
                        ? trim(strtoupper(implode(' ', array_filter([
                            $leadingPassenger->first_name,
                            $leadingPassenger->last_name,
                        ]))))
                        : ($folder->customer_name ?? ''),
                    'pickup_date' => format_invoice_date($transport->service_date),
                    'pickup_time' => format_invoice_time($transport->pickup_time),
                    'vehicle' => $transport->vehicle_type ?? '',
                ];
            })->all(),
            'ziaraats' => array_values(array_filter([
                $folder->makkah_ziarat ? ['label' => 'Ziaraats Makkah', 'status' => 'Included'] : null,
                $folder->madinah_ziarat ? ['label' => 'Ziaraats Madinah', 'status' => 'Included'] : null,
            ])),
            'terms_legal_name' => config('invoice.terms_legal_name'),
            'acceptance' => [],
            'document_ref' => 'FOLDER-'.$folder->id,
            'page' => 1,
            'page_count' => 1,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, FolderHotelDetail>  $hotelDetails
     * @return list<array{label: string, stays: list<array<string, string>>}>
     */
    private function buildHotelItinerary($hotelDetails): array
    {
        $sections = [];

        foreach ($hotelDetails->groupBy(fn (FolderHotelDetail $hotel) => $hotel->hotel_city ?: 'Hotel') as $city => $hotels) {
            $sections[] = [
                'label' => str_contains(strtolower((string) $city), 'madinah')
                    ? 'Madinah Hotel'
                    : (str_contains(strtolower((string) $city), 'makkah') || str_contains(strtolower((string) $city), 'mecca')
                        ? 'Makkah Hotel'
                        : $city.' Hotel'),
                'stays' => $hotels->map(fn (FolderHotelDetail $hotel) => [
                    'hotel_name' => $hotel->hotel_name ?? '',
                    'room_type' => $hotel->type ?? '',
                    'nights' => $hotel->nights !== null ? str_pad((string) $hotel->nights, 2, '0', STR_PAD_LEFT) : '',
                    'check_in' => format_invoice_date($hotel->date_in),
                    'check_out' => format_invoice_date($hotel->date_out),
                    'meal' => $hotel->meals ?? '',
                ])->values()->all(),
            ];
        }

        return $sections;
    }

    /**
     * @param  iterable<int, object>  $models
     * @param  list<string>  $fields
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function filterModelsWithInvoiceData(iterable $models, array $fields)
    {
        return collect($models)
            ->filter(fn (object $model): bool => $this->modelHasInvoiceData($model, $fields))
            ->values();
    }

    /**
     * @param  list<string>  $fields
     */
    private function modelHasInvoiceData(object $model, array $fields): bool
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
