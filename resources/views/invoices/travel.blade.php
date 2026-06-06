<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice_number }} — {{ $company['name'] }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        .invoice-page {
            --inv-font: "Segoe UI", Arial, Helvetica, sans-serif;
            --inv-text-xs: 9px;
            --inv-text-sm: 10px;
            --inv-text-base: 11px;
            --inv-text-md: 12px;
            --inv-leading: 1.42;
            --inv-leading-tight: 1.32;
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 10mm 12mm 12mm;
            background: #fff;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.12);
            font-family: var(--inv-font);
            font-size: var(--inv-text-base);
            line-height: var(--inv-leading);
            color: #111;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            margin: 0;
            font-family: var(--inv-font, "Segoe UI", Arial, Helvetica, sans-serif);
            color: #111;
            background: #e8e8e8;
        }

        @media print {
            body {
                background: #fff;
            }

            .invoice-page {
                margin: 0;
                box-shadow: none;
                width: auto;
                min-height: auto;
            }

            .no-print {
                display: none !important;
            }
        }

        .invoice-header__top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 10px;
            font-size: var(--inv-text-base);
            line-height: var(--inv-leading-tight);
        }

        .invoice-header__meta--right {
            text-align: right;
        }

        .invoice-header__brand {
            text-align: center;
            margin-bottom: 12px;
            font-size: var(--inv-text-sm);
            line-height: var(--inv-leading-tight);
        }

        .invoice-logo {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 8px 18px 6px;
            font-weight: 700;
            font-size: var(--inv-text-md);
            letter-spacing: 0.03em;
        }

        .invoice-website {
            margin-top: 6px;
            color: #222;
        }

        .invoice-brand-email {
            margin-top: 4px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.1rem;
            font-size: var(--inv-text-sm);
            line-height: var(--inv-leading-tight);
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        th {
            font-weight: 700;
            text-align: left;
            background: #fff;
        }

        .table-summary th,
        .table-summary td {
            text-align: center;
            font-size: var(--inv-text-base);
        }

        .table-passengers {
            margin-top: 10px;
        }

        .table-passengers th {
            text-align: center;
            font-size: var(--inv-text-sm);
        }

        .table-passengers td {
            font-size: var(--inv-text-sm);
        }

        .table-passengers td.price {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .table-passengers td.valign-top {
            vertical-align: top;
        }

        .invoice-transactions-paid {
            margin: 0;
            padding-left: 1.15rem;
            text-align: left;
            list-style: decimal;
            font-size: var(--inv-text-sm);
            line-height: var(--inv-leading-tight);
        }

        .invoice-transactions-paid li {
            margin-bottom: 0.35rem;
        }

        .invoice-transactions-paid li:last-child {
            margin-bottom: 0;
        }

        .invoice-transaction-amount {
            display: block;
            font-weight: 700;
        }

        .invoice-transaction-date {
            display: block;
            font-weight: 400;
            color: #333;
        }

        .disclaimer {
            margin: 8px 0 10px;
            text-align: justify;
            font-size: var(--inv-text-xs);
            line-height: 1.45;
            color: #222;
        }

        .payment-wrap {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
        }

        .table-payment {
            width: 42%;
            min-width: 220px;
        }

        .table-payment td:first-child {
            font-weight: 700;
            width: 55%;
        }

        .table-payment td:last-child {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .table-hotels {
            margin-top: 1.1rem;
        }

        .table-hotels th,
        .table-hotels td {
            font-size: var(--inv-text-sm);
        }

        .section-title {
            margin: 0 0 8px;
            text-align: center;
            font-weight: 700;
            font-size: var(--inv-text-md);
            text-decoration: underline;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .itinerary-bar {
            margin: 0;
            padding: 5px 8px;
            text-align: center;
            font-weight: 700;
            font-size: var(--inv-text-sm);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: #d9d9d9;
            border: 1px solid #000;
            border-bottom: none;
        }

        .table-itinerary th {
            text-align: center;
            padding: 4px 3px;
            font-size: var(--inv-text-xs);
            letter-spacing: 0.02em;
        }

        .table-itinerary td {
            min-height: 20px;
            font-size: var(--inv-text-xs);
        }

        .table-hotel-itinerary .hotel-itinerary-section-head th {
            text-align: center;
            padding: 4px 5px;
            font-size: var(--inv-text-xs);
        }

        .table-hotel-itinerary .hotel-itinerary-section-head th:first-child {
            text-align: left;
        }

        .table-hotel-itinerary td {
            text-align: center;
            padding: 4px 5px;
            min-height: 20px;
            font-size: var(--inv-text-xs);
        }

        .table-hotel-itinerary td:first-child {
            text-align: left;
        }

        .table-hotel-itinerary .hotel-itinerary-spacer td {
            height: 10px;
            padding: 0;
        }

        .table-invoice-section th,
        .table-invoice-section td {
            padding: 4px 5px;
            min-height: 20px;
            text-align: left;
            font-size: var(--inv-text-xs);
        }

        .table-other-services th,
        .table-other-services td,
        .table-visa-details th,
        .table-visa-details td {
            text-align: left;
        }

        .invoice-section-title-row th {
            text-align: center;
            font-weight: 700;
            font-size: var(--inv-text-sm);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: #d9d9d9;
            border: 1px solid #000;
            padding: 5px 8px;
        }

        .table-transport th {
            text-align: center;
            padding: 4px 5px;
            font-size: var(--inv-text-xs);
        }

        .table-transport tr:not(.invoice-section-title-row) th:first-child,
        .table-transport tr:not(.invoice-section-title-row) td:first-child {
            text-align: left;
            width: 38%;
        }

        .table-transport .invoice-section-title-row th {
            text-align: center;
        }

        .table-transport td {
            text-align: center;
            font-size: var(--inv-text-xs);
        }

        .invoice-ziaraats {
            margin: 12px 0;
            font-size: var(--inv-text-sm);
            line-height: var(--inv-leading-tight);
        }

        .invoice-ziaraats p {
            margin: 0 0 4px;
        }

        .invoice-ziaraats p:last-child {
            margin-bottom: 0;
        }

        .invoice-terms {
            margin-top: 16px;
            padding-top: 12px;
            border-top: 1px solid #ccc;
            color: #111;
            text-align: justify;
            font-size: var(--inv-text-xs);
            line-height: 1.45;
        }

        .invoice-terms__title {
            margin: 0 0 8px;
            font-weight: 700;
            font-size: var(--inv-text-sm);
        }

        .invoice-terms__heading {
            margin: 10px 0 4px;
            font-weight: 700;
            font-size: var(--inv-text-xs);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .invoice-terms__subheading {
            margin: 8px 0 4px;
            font-weight: 700;
            font-size: var(--inv-text-xs);
        }

        .invoice-terms__disclaimer-title {
            margin: 10px 0 6px;
            font-weight: 700;
            font-size: var(--inv-text-xs);
        }

        .invoice-terms__acknowledgment {
            margin-top: 12px;
            font-style: italic;
            font-size: var(--inv-text-xs);
        }

        .invoice-terms__signature {
            margin-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .invoice-terms__signature-media {
            text-align: center;
        }

        .invoice-terms__signature-media img {
            display: inline-block;
            max-height: 44px;
            max-width: 170px;
        }

        .invoice-terms__signature-logo {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 6px 14px 5px;
            font-weight: 700;
            font-size: var(--inv-text-base);
            letter-spacing: 0.03em;
        }

        .invoice-terms__signature-email {
            font-family: "Segoe Script", "Brush Script MT", cursive;
            font-size: var(--inv-text-sm);
            color: #1a1a1a;
            text-align: left;
        }

        .invoice-terms__signature-date {
            font-size: var(--inv-text-xs);
            color: #333;
            text-align: left;
        }

        .invoice-terms p {
            margin: 0 0 6px;
        }

        .invoice-terms ul {
            margin: 0 0 6px;
            padding-left: 15px;
        }

        .invoice-terms li {
            margin-bottom: 3px;
        }

        .invoice-terms p:last-child,
        .invoice-terms ul:last-child {
            margin-bottom: 0;
        }

        @media print {
            .invoice-terms {
                page-break-before: auto;
            }
        }

        .invoice-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 18px;
            gap: 12px;
            font-size: var(--inv-text-sm);
        }

        .invoice-footer__box {
            background: #d9d9d9;
            border: 1px solid #999;
            padding: 4px 10px;
            color: #333;
        }

        .toolbar {
            max-width: 210mm;
            margin: 12px auto 0;
            display: flex;
            gap: 8px;
        }

        .toolbar button {
            border: 1px solid #254d79;
            background: #10253f;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
        }

        .toolbar button:hover {
            background: #0b192c;
        }
    </style>
</head>

<body>
    <div class="toolbar no-print">
        <button type="button" onclick="window.print()">Print invoice</button>
    </div>

    <article class="invoice-page">
        <header class="invoice-header">
            <div class="invoice-header__top">
                <div>
                    <strong>Booking Date:</strong> <br> {{ $booking_date }}
                </div>
                <div class="invoice-header__meta--right">
                    <div><strong>Direct Line:</strong> {{ $direct_line }}</div>
                </div>
            </div>
            <div class="invoice-header__brand">
                @if (!empty($company['logo_url']))
                    <img src="{{ $company['logo_url'] }}" alt="{{ $company['name'] }}"
                        style="max-height: 48px; max-width: 180px;">
                @else
                    <div class="invoice-logo">{{ $company['name'] }}</div>
                @endif
                @if (!empty($company['email']))
                    <div class="invoice-brand-email">{{ $company['email'] }}</div>
                @endif
                {{-- <div class="invoice-website">{{ $company['website'] }}</div> --}}
            </div>
        </header>

        <table class="table-summary">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Agent</th>
                    <th>Travel Date</th>
                    <th>Destination</th>
                    <th>No. of Passengers</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice_number }}</td>
                    <td>{{ $agent_name }}</td>
                    <td>{{ $travel_date }}</td>
                    <td>{{ $destination }}</td>
                    <td>{{ $passenger_count }}</td>
                </tr>
            </tbody>
        </table>

        <table class="table-passengers">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Flight Details</th>
                    <th>Type</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($passengers as $passenger)
                    <tr>
                        <td>{{ $passenger['title'] }}</td>
                        <td>{{ $passenger['first_name'] }}</td>
                        <td>{{ $passenger['middle_name'] ?: ' ' }}</td>
                        <td>{{ $passenger['last_name'] }}</td>
                        <td>{{ $passenger['flight_details'] }}</td>
                        <td>{{ $passenger['type'] }}</td>
                        <td class="price">£ {{ number_format($passenger['price'], 0) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="7">
                        <p class="disclaimer">
                            Due to airline and immigration regulations, it is imperative that the title(s)/first and
                            middle
                            name(s)/family name(s) shown above match exactly the details shown in your passport. Middle
                            names are
                            required for when traveling to certain countries. Hyphens, apostrophes, spaces and lower
                            case letters will
                            not appear in airline reservations. Please contact us immediately if this is not the case.
                            Any change to
                            these names may incur amendment or cancellation fees.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td colspan="7"><strong>Payment Details</strong></td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td colspan="2">Invoice Total:</td>
                    <td class="price">£ {{ number_format($invoice_total, 0) }}</td>
                </tr>
                @if (!empty($approved_payments))
                    <tr>
                        <td colspan="4"></td>
                        <td colspan="2" class="valign-top">Transactions paid:</td>
                        <td class="price valign-top">
                            <ol class="invoice-transactions-paid">
                                @foreach ($approved_payments as $payment)
                                    <li>
                                        <span
                                            class="invoice-transaction-amount">{{ $payment['amount_formatted'] }}</span>
                                        <span class="invoice-transaction-date">{{ $payment['payment_date'] }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </td>
                    </tr>
                @endif
                <tr>
                    <td colspan="4"></td>
                    <td colspan="2">Amount Due:</td>
                    <td class="price">£ {{ number_format($amount_due, 0) }}</td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td colspan="2">Balance Due Date:</td>
                    <td>{{ $balance_due_date }}</td>
                </tr>
            </tbody>
        </table>

        @php
            $hasPackageDetails =
                !empty($flight_itinerary) ||
                !empty($hotel_itinerary) ||
                !empty($visa_details) ||
                !empty($other_details) ||
                !empty($transport);
        @endphp

        @if ($hasPackageDetails)
            <h2 class="section-title">PACKAGE DETAILS</h2>
        @endif

        @if (!empty($flight_itinerary))
            <p class="itinerary-bar">FLIGHT ITINERARY</p>
            <table class="table-itinerary">
                <thead>
                    <tr>
                        <th>Operated by</th>
                        <th>Flight No</th>
                        <th>Departure Date</th>
                        <th>Departure Time</th>
                        <th>From</th>
                        <th>Arrival Time</th>
                        <th>To</th>
                        <th>Arrival Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($flight_itinerary as $leg)
                        <tr>
                            <td>{{ $leg['operated_by'] ?? '' }}</td>
                            <td>{{ $leg['flight_no'] ?? '' }}</td>
                            <td>{{ $leg['departure_date'] ?? '' }}</td>
                            <td>{{ $leg['departure_time'] ?? '' }}</td>
                            <td>{{ $leg['from'] ?? '' }}</td>
                            <td>{{ $leg['arrival_time'] ?? '' }}</td>
                            <td>{{ $leg['to'] ?? '' }}</td>
                            <td>{{ $leg['arrival_date'] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if (!empty($hotel_itinerary))
            <p class="itinerary-bar">HOTEL ITINERARY</p>
            <table class="table-itinerary table-hotel-itinerary">
                <tbody>
                    @foreach ($hotel_itinerary as $sectionIndex => $section)
                        @if ($sectionIndex > 0)
                            <tr class="hotel-itinerary-spacer">
                                <td colspan="6">&nbsp;</td>
                            </tr>
                        @endif
                        <tr class="hotel-itinerary-section-head">
                            <th>{{ $section['label'] }}</th>
                            <th>Room Type</th>
                            <th>Nights</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Meal</th>
                        </tr>
                        @foreach ($section['stays'] as $stay)
                            <tr>
                                <td>{{ $stay['hotel_name'] }}</td>
                                <td>{{ $stay['room_type'] }}</td>
                                <td>{{ $stay['nights'] }}</td>
                                <td>{{ $stay['check_in'] }}</td>
                                <td>{{ $stay['check_out'] }}</td>
                                <td>{{ $stay['meal'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @endif

        @if (!empty($visa_details))
            <p class="itinerary-bar">VISA DETAILS</p>
            <table class="table-itinerary table-invoice-section table-visa-details">
                <tbody>
                    <tr>
                        <th>Supplier</th>
                        <th>Description</th>
                    </tr>
                    @foreach ($visa_details as $visa)
                        <tr>
                            <td>{{ $visa['supplier'] ?: '—' }}</td>
                            <td>{{ $visa['description'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if (!empty($other_details))
            <table class="table-itinerary table-invoice-section table-other-services">
                <tbody>
                    <tr class="invoice-section-title-row">
                        <th colspan="2">OTHER DETAILS</th>
                    </tr>
                    <tr>
                        <th>Supplier</th>
                        <th>Description</th>
                    </tr>
                    @foreach ($other_details as $detail)
                        <tr>
                            <td>{{ $detail['supplier'] ?: '—' }}</td>
                            <td>{{ $detail['description'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if (!empty($transport))
            <table class="table-itinerary table-invoice-section table-transport">
                <tbody>
                    <tr class="invoice-section-title-row">
                        <th colspan="5">TRANSPORT</th>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <th>Leading passenger</th>
                        <th>Pickup date</th>
                        <th>Pickup time</th>
                        <th>Vehicle</th>
                    </tr>
                    @foreach ($transport as $trip)
                        <tr>
                            <td>{{ $trip['description'] }}</td>
                            <td>{{ $trip['leading_passenger'] }}</td>
                            <td>{{ $trip['pickup_date'] }}</td>
                            <td>{{ $trip['pickup_time'] }}</td>
                            <td>{{ $trip['vehicle'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if (!empty($ziaraats))
            <div class="invoice-ziaraats">
                @foreach ($ziaraats as $ziaraat)
                    <p><strong>{{ $ziaraat['label'] }}:</strong> {{ $ziaraat['status'] }}</p>
                @endforeach
            </div>
        @endif

        @include('invoices.partials.terms-and-conditions', [
            'terms_legal_name' => $terms_legal_name ?? config('invoice.terms_legal_name'),
            'company' => $company ?? config('invoice.company'),
            'acceptance' => $acceptance ?? [],
        ])

    </article>
</body>

</html>
