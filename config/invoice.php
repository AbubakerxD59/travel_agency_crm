<?php

return [

    'company' => [
        'name' => env('INVOICE_COMPANY_NAME', 'HAQ Travels'),
        'email' => env('INVOICE_EMAIL', 'info@haramtravel.uk'),
        'phone' => env('INVOICE_PHONE', '+44 20 3827 3330'),
        'website' => env('INVOICE_WEBSITE', 'https://haqtravels.co.uk/'),
        'logo_url' => env('INVOICE_LOGO_URL'),
    ],

    /*
    | Legal entity name used in invoice terms & conditions.
    */
    'terms_legal_name' => env('INVOICE_TERMS_LEGAL_NAME', 'Bukhari Travel Ltd T/A Haram Travel'),

    /*
    | Company-specific payment instructions shown on invoices after the ziarat section.
    | Keys must match the company name on the folder (case-insensitive).
    */
    'company_payment_sections' => [
        'Al Kabir Travel' => [
            'intro' => [
                'For your security, please ensure all payments are made only to the company bank account listed Below.',
                'Bukhari Travel T/A Haram Travel will not accept responsibility for payments made to any personal bank account or third party unless officially confirmed in writing by the company.',
            ],
            'bank_details' => [
                ['label' => 'Bank Name', 'value' => 'HSBC'],
                ['label' => 'Account Name', 'value' => 'Bukhari Travel Limited'],
                ['label' => 'Sort Code', 'value' => '40-11-56'],
                ['label' => 'Account Number', 'value' => '01114646'],
            ],
        ],
    ],
];
