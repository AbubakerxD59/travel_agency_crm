<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\View\View;

class InvoicePreviewController extends Controller
{
    public function test(): View
    {
        return view('invoices.travel', $this->sampleInvoice4109());
    }

    /**
     * Sample data matching reference invoice #4109 (HAQ Travels).
     *
     * @return array<string, mixed>
     */
    private function sampleInvoice4109(): array
    {
        $passengers = [
            ['title' => 'Ms', 'first_name' => 'FAIZA', 'middle_name' => '', 'last_name' => 'ALI', 'flight_details' => 'EDI to JED', 'type' => 'Adult', 'price' => 1086],
            ['title' => 'Mr', 'first_name' => 'AHMED', 'middle_name' => '', 'last_name' => 'ALI', 'flight_details' => 'EDI to JED', 'type' => 'Adult', 'price' => 1086],
            ['title' => 'Ms', 'first_name' => 'AYESHA', 'middle_name' => '', 'last_name' => 'ALI', 'flight_details' => 'EDI to JED', 'type' => 'Adult', 'price' => 1086],
            ['title' => 'Mr', 'first_name' => 'HASSAN', 'middle_name' => '', 'last_name' => 'ALI', 'flight_details' => 'EDI to JED', 'type' => 'Adult', 'price' => 1086],
            ['title' => 'Ms', 'first_name' => 'FATIMA', 'middle_name' => '', 'last_name' => 'ALI', 'flight_details' => 'EDI to JED', 'type' => 'Adult', 'price' => 1086],
            ['title' => 'Mr', 'first_name' => 'OMAR', 'middle_name' => '', 'last_name' => 'ALI', 'flight_details' => 'EDI to JED', 'type' => 'Adult', 'price' => 1086],
        ];

        return [
            'company' => config('invoice.company'),
            'booking_date' => format_invoice_date(Carbon::parse('2026-02-08')),
            'invoice_number' => '4109',
            'agent_name' => 'Muhammad Zain',
            'travel_date' => format_invoice_date(Carbon::parse('2026-02-21')),
            'destination' => "Jeddah - King Abdulaziz Int'l",
            'passenger_count' => count($passengers),
            'passengers' => $passengers,
            'invoice_total' => 6516,
            'amount_due' => 5916,
            'approved_payments' => [
                [
                    'amount_formatted' => '£ 600',
                    'payment_date' => format_invoice_date(Carbon::parse('2026-02-10')),
                ],
            ],
            'balance_due_date' => format_invoice_date(Carbon::parse('2026-02-11')),
            'hotels' => [
                [
                    'name' => 'Voco Makkah',
                    'city' => 'Makkah',
                    'nights' => '04',
                    'rooms' => '02',
                    'type' => 'Triple Room',
                ],
            ],
            'flight_itinerary' => [],
            'hotel_itinerary' => [
                [
                    'label' => 'Makkah Hotel',
                    'stays' => [
                        [
                            'hotel_name' => 'Voco Makkah',
                            'room_type' => 'Triple Room',
                            'nights' => '04',
                            'check_in' => format_invoice_date(Carbon::parse('2026-02-21')),
                            'check_out' => format_invoice_date(Carbon::parse('2026-02-25')),
                            'meal' => 'Room Only',
                        ],
                        [
                            'hotel_name' => 'Voco Makkah',
                            'room_type' => 'Triple Room',
                            'nights' => '02',
                            'check_in' => format_invoice_date(Carbon::parse('2026-03-01')),
                            'check_out' => format_invoice_date(Carbon::parse('2026-03-03')),
                            'meal' => 'Room Only',
                        ],
                    ],
                ],
                [
                    'label' => 'Madinah Hotel',
                    'stays' => [
                        [
                            'hotel_name' => 'Karam Tibah Almaasi',
                            'room_type' => 'Triple Room',
                            'nights' => '04',
                            'check_in' => format_invoice_date(Carbon::parse('2026-02-25')),
                            'check_out' => format_invoice_date(Carbon::parse('2026-03-01')),
                            'meal' => 'Room Only',
                        ],
                    ],
                ],
            ],
            'other_services' => [
                ['description' => '06x EVW Visa'],
            ],
            'transport' => [
                [
                    'description' => 'Full Loop Transportation+ Ziyarah',
                    'leading_passenger' => 'FAIZA ALI',
                    'pickup_date' => format_invoice_date(Carbon::parse('2026-02-21')),
                    'pickup_time' => format_invoice_time('00:00'),
                    'vehicle' => 'H1',
                ],
                [
                    'description' => 'Full Loop Transportation+ Ziyarah',
                    'leading_passenger' => 'FAIZA ALI',
                    'pickup_date' => format_invoice_date(Carbon::parse('2026-02-25')),
                    'pickup_time' => format_invoice_time('00:00'),
                    'vehicle' => 'H1',
                ],
                [
                    'description' => 'Full Loop Transportation+ Ziyarah',
                    'leading_passenger' => 'FAIZA ALI',
                    'pickup_date' => format_invoice_date(Carbon::parse('2026-03-01')),
                    'pickup_time' => format_invoice_time('00:00'),
                    'vehicle' => 'H1',
                ],
                [
                    'description' => 'Full Loop Transportation+ Ziyarah',
                    'leading_passenger' => 'FAIZA ALI',
                    'pickup_date' => format_invoice_date(Carbon::parse('2026-03-03')),
                    'pickup_time' => format_invoice_time('00:00'),
                    'vehicle' => 'H1',
                ],
            ],
            'ziaraats' => [
                ['label' => 'Ziaraats Makkah', 'status' => 'Included'],
                ['label' => 'Ziaraats Madinah', 'status' => 'Included'],
            ],
            'terms_legal_name' => config('invoice.terms_legal_name'),
            'acceptance' => [
                'signer_email' => 'faiza.ali74@yahoo.co.uk',
                'signed_date' => format_invoice_date(Carbon::parse('2026-02-08')),
            ],
            'document_ref' => 'XTWNA-4SK4W-AJ3NN-FYFKT',
            'page' => 1,
            'page_count' => 11,
        ];
    }
}
