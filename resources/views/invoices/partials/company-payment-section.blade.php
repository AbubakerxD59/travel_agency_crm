@if (! empty($company_payment_section))
    <section class="invoice-company-payment">
        <p class="invoice-company-payment__notice-title">IMPORTANT PAYMENT NOTICE</p>

        @foreach ($company_payment_section['intro'] ?? [] as $paragraph)
            <p class="invoice-company-payment__intro">{{ $paragraph }}</p>
        @endforeach

        @if (! empty($company_payment_section['bank_details']))
            <div class="invoice-company-payment__bank-details">
                @foreach ($company_payment_section['bank_details'] as $detail)
                    @php
                        $detailValue = trim((string) ($detail['value'] ?? ''));
                    @endphp
                    <p>
                        <strong>{{ $detail['label'] }}:</strong>@if ($detailValue !== '') {{ $detailValue }}@endif
                    </p>
                @endforeach
            </div>
        @endif
    </section>
@endif
