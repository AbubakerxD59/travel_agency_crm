@if (! empty($company_payment_section))
    <section class="invoice-company-payment">
        @foreach ($company_payment_section['intro'] ?? [] as $paragraph)
            <p>{{ $paragraph }}</p>
        @endforeach

        @if (! empty($company_payment_section['bank_details']))
            <div class="invoice-company-payment__bank-details">
                @foreach ($company_payment_section['bank_details'] as $detail)
                    <p><strong>{{ $detail['label'] }}:</strong> {{ $detail['value'] }}</p>
                @endforeach
            </div>
        @endif
    </section>
@endif
