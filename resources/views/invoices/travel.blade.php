<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice_number }} — {{ $company['name'] }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 8mm;
        }

        * {
            box-sizing: border-box;
        }

        :root {
            --inv-font: "Times New Roman", Times, serif;
            --inv-text-xs: 10pt;
            --inv-text-sm: 11pt;
            --inv-text-base: 12pt;
            --inv-text-md: 13pt;
            --inv-table-heading: 11pt;
            --inv-leading: 1.45;
            --inv-leading-tight: 1.35;
            --inv-section-gap: 20px;
            --inv-cell-padding-y: 7px;
            --inv-cell-padding-x: 10px;
        }

        .invoice-page {
            width: 210mm;
            min-height: 297mm;
            margin: 16px auto;
            padding: 8mm 6mm 10mm;
            background: #fff;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.12);
            font-family: var(--inv-font);
            font-size: var(--inv-text-base);
            font-weight: 400;
            line-height: var(--inv-leading);
            color: #111;
        }

        body {
            margin: 0;
            font-family: var(--inv-font);
            font-size: var(--inv-text-base);
            font-weight: 400;
            line-height: var(--inv-leading);
            color: #111;
            background: #e8e8e8;
        }

        .invoice-page table,
        .invoice-page th,
        .invoice-page td,
        .invoice-page p,
        .invoice-page h2,
        .invoice-page strong,
        .invoice-page li {
            font-family: inherit;
        }

        body.invoice-pdf {
            background: #fff;
        }

        body.invoice-pdf .invoice-page {
            margin: 0;
            box-shadow: none;
            width: 100%;
            max-width: 100%;
            min-height: auto;
            padding: 0;
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

        .invoice-header {
            margin-bottom: var(--inv-section-gap);
        }

        .invoice-header__top {
            margin-bottom: 14px;
            font-size: var(--inv-text-base);
            line-height: var(--inv-leading-tight);
        }

        .invoice-header__meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 16px;
            align-items: baseline;
        }

        .invoice-header__meta-grid .invoice-header__meta--right {
            text-align: right;
        }

        .invoice-header__brand {
            text-align: center;
            margin-bottom: 14px;
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
            border-collapse: separate;
            border-spacing: 3px;
            margin-bottom: var(--inv-section-gap);
            font-size: var(--inv-text-sm);
            line-height: var(--inv-leading-tight);
        }

        th,
        td {
            border: 1px solid #000;
            padding: var(--inv-cell-padding-y) var(--inv-cell-padding-x);
            vertical-align: top;
        }

        th {
            font-weight: 700;
            text-align: left;
            background: #fff;
            font-size: var(--inv-table-heading);
            white-space: nowrap;
        }

        .table-invoice-summary {
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: var(--inv-section-gap);
        }

        .table-invoice-summary th,
        .table-invoice-summary td {
            text-align: center;
        }

        .table-invoice-summary td {
            font-size: var(--inv-text-base);
        }

        .table-invoice-primary {
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: var(--inv-section-gap);
        }

        .table-invoice-primary .invoice-passenger-head th {
            text-align: center;
        }

        .table-invoice-primary td {
            font-size: var(--inv-text-sm);
        }

        .table-invoice-primary td.price {
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .table-invoice-primary td.valign-top {
            vertical-align: top;
        }

        .table-hotels {
            border-collapse: separate;
            border-spacing: 3px;
        }

        .table-itinerary,
        .table-hotel-itinerary,
        .table-invoice-section,
        .table-other-services,
        .table-visa-details,
        .table-transport {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .itinerary-bar + table {
            margin-top: 0;
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
            margin: 10px 0 12px;
            text-align: justify;
            font-size: var(--inv-text-base);
            line-height: var(--inv-leading);
            color: #111;
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
            margin-top: var(--inv-section-gap);
        }

        .table-hotels td {
            font-size: var(--inv-text-sm);
        }

        .section-title {
            margin: var(--inv-section-gap) 0 12px;
            text-align: center;
            font-weight: 700;
            font-size: var(--inv-text-md);
            text-decoration: underline;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .itinerary-bar {
            margin: var(--inv-section-gap) 0 0;
            padding: 6px 10px;
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
            padding: var(--inv-cell-padding-y) 5px;
            letter-spacing: 0.02em;
        }

        .table-itinerary td {
            min-height: 20px;
            font-size: var(--inv-text-xs);
        }

        .table-hotel-itinerary .hotel-itinerary-section-head th {
            text-align: center;
            padding: var(--inv-cell-padding-y) 6px;
        }

        .table-hotel-itinerary .hotel-itinerary-section-head th:first-child {
            text-align: left;
        }

        .table-hotel-itinerary td {
            text-align: center;
            padding: var(--inv-cell-padding-y) 6px;
            min-height: 20px;
            font-size: var(--inv-text-xs);
        }

        .table-hotel-itinerary td:first-child {
            text-align: left;
        }

        .table-hotel-itinerary .hotel-itinerary-spacer td {
            height: 14px;
            padding: 0;
            border: none;
        }

        .table-invoice-section td {
            padding: var(--inv-cell-padding-y) 6px;
            min-height: 20px;
            text-align: left;
            font-size: var(--inv-text-xs);
        }

        .table-invoice-section th {
            padding: var(--inv-cell-padding-y) 6px;
            min-height: 20px;
            text-align: left;
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
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: #d9d9d9;
            border: 1px solid #000;
            padding: 6px 10px;
        }

        .table-transport th {
            text-align: center;
            padding: var(--inv-cell-padding-y) 6px;
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
            margin: var(--inv-section-gap) 0;
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
            margin: calc(var(--inv-section-gap) + 4px) 20px 0;
            padding-top: 14px;
            border-top: 1px solid #ccc;
            color: #111;
            text-align: left;
            font-family: var(--inv-font);
            font-size: var(--inv-text-base);
            font-weight: 400;
            line-height: var(--inv-leading);
            page-break-before: always;
            break-before: page;
        }

        .invoice-terms-pdf {
            margin-top: var(--inv-section-gap);
            width: 100%;
        }

        .invoice-terms-pdf__embed {
            display: block;
            width: 100%;
            min-height: 1120px;
            border: none;
        }

        .invoice-terms-pdf__fallback {
            margin: 0;
            font-size: var(--inv-text-sm);
            text-align: center;
        }

        .invoice-terms-pdf__fallback a {
            color: #10253f;
            font-weight: 600;
        }

        .invoice-terms__title {
            margin: 0 0 10px;
            font-weight: 700;
            font-size: var(--inv-text-md);
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .invoice-terms__heading {
            margin: 14px 0 6px;
            padding-top: 12px;
            border-top: 1px solid #bbb;
            font-weight: 700;
            font-size: calc(var(--inv-text-base) + 2px);
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .invoice-terms__heading:first-of-type {
            margin-top: 8px;
            padding-top: 0;
            border-top: none;
        }

        .invoice-terms__subheading {
            margin: 10px 0 4px;
            font-weight: 700;
            font-size: var(--inv-text-base);
            text-transform: none;
        }

        .invoice-terms__disclaimer-title {
            margin: 10px 0 6px;
            font-weight: 700;
            font-size: var(--inv-text-base);
        }

        .invoice-terms__acknowledgment {
            margin-top: 12px;
            font-style: italic;
            font-size: var(--inv-text-base);
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
            font-family: var(--inv-font);
            font-size: var(--inv-text-sm);
            font-style: italic;
            color: #111;
            text-align: left;
        }

        .invoice-terms__signature-date {
            font-size: var(--inv-text-sm);
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

        .toolbar button:disabled {
            opacity: 0.6;
            cursor: wait;
        }
    </style>
</head>

<body @class(['invoice-pdf' => ! empty($for_pdf)])>
    @empty($for_pdf)
        <div class="toolbar no-print">
            @if (!empty($pdf_download_url))
                <button type="button" id="invoice-download-pdf">Download Invoice</button>
            @endif
        </div>
    @endempty

    <article class="invoice-page">
        <header class="invoice-header">
            <div class="invoice-header__top">
                <div class="invoice-header__meta-grid">
                    <div><strong>Direct Line:</strong> {{ $direct_line }}</div>
                    <div class="invoice-header__meta--right"><strong>Booking Date:</strong></div>
                    @if (! empty($agent_email))
                        <div>{{ $agent_email }}</div>
                    @else
                        <div></div>
                    @endif
                    <div class="invoice-header__meta--right">{{ $booking_date }}</div>
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

        <table class="table-invoice-summary">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Travel Consultant</th>
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

        <table class="table-invoice-primary">
            <thead>
                <tr class="invoice-passenger-head">
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
                        <th>Description</th>
                    </tr>
                    @foreach ($visa_details as $visa)
                        <tr>
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

        @include('invoices.partials.terms-and-conditions')

    </article>

    @if (empty($for_pdf) && !empty($pdf_download_url))
        <script>
            (function() {
                const pdfUrl = @json($pdf_download_url);
                const downloadButton = document.getElementById('invoice-download-pdf');

                if (!downloadButton) {
                    return;
                }

                function parseFilename(contentDisposition, fallback) {
                    if (!contentDisposition) {
                        return fallback;
                    }

                    const utf8Match = contentDisposition.match(/filename\*=UTF-8''([^;]+)/i);
                    if (utf8Match?.[1]) {
                        try {
                            return decodeURIComponent(utf8Match[1]);
                        } catch {
                            return utf8Match[1];
                        }
                    }

                    const quotedMatch = contentDisposition.match(/filename="([^"]+)"/i);
                    if (quotedMatch?.[1]) {
                        return quotedMatch[1];
                    }

                    const plainMatch = contentDisposition.match(/filename=([^;]+)/i);
                    if (plainMatch?.[1]) {
                        return plainMatch[1].trim();
                    }

                    return fallback;
                }

                downloadButton.addEventListener('click', async function() {
                    downloadButton.disabled = true;

                    try {
                        const response = await fetch(pdfUrl, {
                            credentials: 'same-origin',
                            headers: {
                                Accept: 'application/pdf',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Could not download invoice. Please try again.');
                        }

                        const blob = await response.blob();
                        const filename = parseFilename(
                            response.headers.get('Content-Disposition'),
                            'Invoice.pdf',
                        );
                        const objectUrl = URL.createObjectURL(blob);
                        const downloadLink = document.createElement('a');

                        downloadLink.href = objectUrl;
                        downloadLink.download = filename;
                        downloadLink.style.display = 'none';
                        document.body.appendChild(downloadLink);
                        downloadLink.click();
                        downloadLink.remove();
                        URL.revokeObjectURL(objectUrl);
                    } catch (error) {
                        const message = error instanceof Error ?
                            error.message :
                            'Could not download invoice.';

                        window.alert(message);
                    } finally {
                        downloadButton.disabled = false;
                    }
                });
            })();
        </script>
    @endif
</body>

</html>
