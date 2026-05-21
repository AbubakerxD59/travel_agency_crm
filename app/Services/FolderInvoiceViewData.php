<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\FolderHotelDetail;

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

        return [
            'company' => [
                'name' => $folder->company?->name ?? $companyConfig['name'],
                'email' => $companyConfig['email'],
                'phone' => $companyConfig['phone'],
                'website' => $folder->company?->website_link ?? $companyConfig['website'],
                'logo_url' => $folder->company?->imageUrl() ?? $companyConfig['logo_url'],
            ],
            'booking_date' => format_invoice_date($folder->booking_date ?? $folder->created_at ?? now()),
            'invoice_number' => $folder->vendor_reference ?: (string) $folder->id,
            'agent_name' => $folder->agent?->name ?? '—',
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
            'balance_due_date' => format_invoice_date($folder->balance_due_date),
            'hotels' => $folder->hotelDetails->map(fn (FolderHotelDetail $hotel) => [
                'name' => $hotel->hotel_name ?? '—',
                'city' => $hotel->hotel_city ?? '—',
                'nights' => $hotel->nights !== null ? str_pad((string) $hotel->nights, 2, '0', STR_PAD_LEFT) : '—',
                'rooms' => $hotel->rooms ?? '—',
                'type' => $hotel->type ?? '—',
            ])->all(),
            'flight_itinerary' => $folder->itineraries->map(fn ($leg) => [
                'operated_by' => $leg->airline_code ?? '',
                'flight_no' => $leg->airline_number ?? '',
                'departure_date' => format_invoice_date($leg->departure_date),
                'departure_time' => format_invoice_time($leg->departure_time),
                'from' => $leg->departure_airport ?? '',
                'arrival_time' => format_invoice_time($leg->arrival_time),
                'to' => $leg->arrival_airport ?? '',
                'arrival_date' => format_invoice_date($leg->arrival_date),
            ])->all(),
            'hotel_itinerary' => $this->buildHotelItinerary($folder),
            'other_services' => collect()
                ->merge($folder->visaDetails->map(fn ($visa) => [
                    'description' => $visa->description ?? 'Visa service',
                ]))
                ->merge($folder->otherDetails->map(fn ($other) => [
                    'description' => $other->description ?? 'Other service',
                ]))
                ->values()
                ->all(),
            'transport' => $folder->transportDetails->map(function ($transport) use ($folder) {
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
     * @return list<array{label: string, stays: list<array<string, string>>}>
     */
    private function buildHotelItinerary(Folder $folder): array
    {
        $sections = [];

        foreach ($folder->hotelDetails->groupBy(fn (FolderHotelDetail $hotel) => $hotel->hotel_city ?: 'Hotel') as $city => $hotels) {
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
}
