<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transportation Voucher {{ $voucher_number ?? '' }}</title>
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

        body {
            margin: 0;
            font-family: var(--inv-font);
            font-size: var(--inv-text-base);
            font-weight: 400;
            line-height: var(--inv-leading);
            color: #111;
            background: #e8e8e8;
        }

        body.tv-pdf {
            background: #fff;
            font-family: sans-serif;
        }

        body.tv-pdf .tv-page,
        body.tv-pdf .invoice-terms {
            font-family: sans-serif;
        }

        .tv-page {
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

        .tv-page table,
        .tv-page th,
        .tv-page td,
        .tv-page p,
        .tv-page h1,
        .tv-page h2,
        .tv-page strong,
        .tv-page li {
            font-family: inherit;
        }

        body.tv-pdf .tv-page {
            margin: 0;
            box-shadow: none;
            width: 100%;
            max-width: 100%;
            min-height: auto;
            padding: 0;
        }

        body.tv-pdf .tv-details__title {
            page-break-before: avoid;
            break-before: avoid;
            page-break-after: avoid;
            break-after: avoid;
        }

        @media print {
            body {
                background: #fff;
            }

            .tv-page {
                margin: 0;
                box-shadow: none;
                width: auto;
                min-height: auto;
            }

            .no-print {
                display: none !important;
            }
        }

        .tv-green {
            color: #2f8f2f;
            font-weight: 700;
        }

        .tv-header {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: start;
            gap: 12px;
            margin-bottom: var(--inv-section-gap);
        }

        .tv-header__left {
            font-size: var(--inv-text-base);
            line-height: var(--inv-leading-tight);
        }

        .tv-header__timestamp {
            margin: 0 0 10px;
            color: #111;
        }

        .tv-header__booking-label {
            margin: 0;
        }

        .tv-header__booking-value {
            margin: 2px 0 0;
        }

        .tv-header__title {
            margin: 0;
            padding-top: 2px;
            font-size: var(--inv-text-md);
            font-weight: 700;
            letter-spacing: 0.06em;
            text-align: center;
            text-transform: uppercase;
        }

        .tv-header__right {
            text-align: right;
            font-size: var(--inv-text-base);
            line-height: var(--inv-leading-tight);
        }

        .tv-header__right p {
            margin: 0 0 6px;
        }

        .tv-header__right p:last-child {
            margin-bottom: 0;
        }

        .tv-brand {
            text-align: center;
            margin: 0 auto var(--inv-section-gap);
            font-size: var(--inv-text-sm);
            line-height: var(--inv-leading-tight);
        }

        .tv-logo-frame {
            display: inline-block;
            padding: 4px;
            border: 3px solid transparent;
            border-radius: 2px;
            background:
                linear-gradient(#fff, #fff) padding-box,
                linear-gradient(90deg, #f39c12, #f7d774) border-box;
        }

        .tv-logo-inner {
            width: 100%;
            height: 100%;
        }

        .tv-logo-inner img {
            display: block;
            max-width: 88px;
            max-height: 88px;
            object-fit: contain;
        }

        .tv-logo-fallback {
            display: inline-block;
            padding: 8px 18px 6px;
            background: #000;
            color: #fff;
            font-size: var(--inv-text-md);
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .tv-website {
            margin-top: 6px;
            color: #222;
        }

        .tv-details {
            max-width: 520px;
            margin-bottom: var(--inv-section-gap);
        }

        .tv-details__title {
            margin: 0 0 12px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .tv-details__table {
            width: 100%;
            max-width: 520px;
            border-collapse: collapse;
            border-spacing: 0;
            margin: 0 0 var(--inv-section-gap);
            font-size: var(--inv-text-base);
            line-height: var(--inv-leading);
        }

        .tv-details__table th,
        .tv-details__table td {
            border: none !important;
            padding: 4px 0;
            vertical-align: top;
            text-align: left;
            background: transparent;
            font-weight: 400;
            white-space: normal;
        }

        .tv-details__table th {
            width: 170px;
            padding-right: 18px;
            font-weight: 700;
        }

        .tv-details__label {
            font-weight: 700;
        }

        .tv-details__value {
            font-weight: 400;
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

        .table-itinerary,
        .table-invoice-section,
        .table-transport {
            border-collapse: collapse;
            border-spacing: 0;
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

        .itinerary-bar + table {
            margin-top: 0;
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

        .invoice-terms h2,
        .invoice-terms h3,
        .invoice-terms p,
        .invoice-terms ul,
        .invoice-terms li {
            font-family: inherit;
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

        .toolbar {
            max-width: 210mm;
            margin: 12px auto 0;
            display: flex;
            align-items: center;
            gap: 12px;
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

<body @class(['tv-pdf' => !empty($for_pdf)])>
    @empty($for_pdf)
        <div class="toolbar no-print">
            @if (!empty($pdf_download_url))
                <button type="button" id="tv-download-pdf">Download PDF</button>
            @endif
        </div>
    @endempty

    <article class="tv-page">
        <header class="tv-header">
            <div class="tv-header__left">
                @empty($for_pdf)
                    <p class="tv-header__timestamp">{{ $printed_at ?? now()->format('n/j/y, g:i A') }}</p>
                @endempty
                <p class="tv-header__booking-label tv-green">Booking Date:</p>
                <p class="tv-header__booking-value">{{ $booking_date ?? '' }}</p>
            </div>

            <h1 class="tv-header__title">Transportation Voucher</h1>

            <div class="tv-header__right">
                @if (!empty($agent_email))
                    <p><span class="tv-green">Email:</span> {{ $agent_email }}</p>
                @endif
                @if (!empty($direct_line) && $direct_line !== '—')
                    <p><span class="tv-green">Direct Line:</span> {{ $direct_line ?? '—' }}</p>
                @endif
            </div>
        </header>

        <section class="tv-brand">
            <div class="tv-logo-frame">
                <div class="tv-logo-inner">
                    @if (!empty($company['logo_url']))
                        <img src="{{ $company['logo_url'] }}" alt="{{ $company['name'] ?? 'Company logo' }}">
                    @else
                        <span class="tv-logo-fallback">{{ $company['name'] ?? 'HAQ Travels' }}</span>
                    @endif
                </div>
            </div>
            @if (!empty($company['website']))
                <p class="tv-website">{{ $company['website'] }}</p>
            @endif
        </section>

        <section class="tv-details">
            <h2 class="tv-details__title">Transportation Voucher</h2>

            <table class="tv-details__table">
                <tbody>
                    <tr>
                        <th class="tv-details__label">Internal Ref No</th>
                        <td class="tv-details__value">{{ $internal_ref_no ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="tv-details__label">Voucher Number</th>
                        <td class="tv-details__value">{{ $voucher_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="tv-details__label">Lead Guest Name</th>
                        <td class="tv-details__value">{{ $lead_guest_name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="tv-details__label">Pax Mobile No</th>
                        <td class="tv-details__value">{{ $pax_mobile ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="tv-details__label">No. Of Pax</th>
                        <td class="tv-details__value">{{ $pax_summary ?? '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        @if (!empty($transport))
            <table class="table-itinerary table-invoice-section table-transport">
                <tbody>
                    <tr class="invoice-section-title-row">
                        <th colspan="5">Transport</th>
                    </tr>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>Pickup Time</th>
                        <th>Pickup Date</th>
                        <th>Vehicle</th>
                    </tr>
                    @foreach ($transport as $trip)
                        <tr>
                            <td>{{ $trip['from'] ?: '—' }}</td>
                            <td>{{ $trip['to'] ?: '—' }}</td>
                            <td>{{ $trip['pickup_time'] ?: '—' }}</td>
                            <td>{{ $trip['pickup_date'] ?: '—' }}</td>
                            <td>{{ $trip['vehicle'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if (!empty($flight_itinerary))
            <p class="itinerary-bar">Flight Itinerary</p>
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
                            <td>{{ $leg['operated_by'] ?: '—' }}</td>
                            <td>{{ $leg['flight_no'] ?: '—' }}</td>
                            <td>{{ $leg['departure_date'] ?: '—' }}</td>
                            <td>{{ $leg['departure_time'] ?: '—' }}</td>
                            <td>{{ $leg['from'] ?: '—' }}</td>
                            <td>{{ $leg['arrival_time'] ?: '—' }}</td>
                            <td>{{ $leg['to'] ?: '—' }}</td>
                            <td>{{ $leg['arrival_date'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @include('transportation-vouchers.partials.terms-and-conditions')
    </article>

    @if (empty($for_pdf) && !empty($pdf_download_url))
        <script>
            (function() {
                const pdfUrl = @json($pdf_download_url);
                const downloadButton = document.getElementById('tv-download-pdf');

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
                            throw new Error('Could not download transportation voucher. Please try again.');
                        }

                        const blob = await response.blob();
                        const filename = parseFilename(
                            response.headers.get('Content-Disposition'),
                            'Transportation-Voucher.pdf',
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
                            'Could not download transportation voucher.';

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
