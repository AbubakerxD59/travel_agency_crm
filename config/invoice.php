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
    | Default legal entity name used in invoice terms & conditions (Haram Travels).
    */
    'terms_legal_name' => env('INVOICE_TERMS_LEGAL_NAME', 'Haram Travel'),

    /*
    | Company-specific legal names for terms & conditions.
    | Keys must match the company name on the folder (case-insensitive).
    */
    'terms_legal_names' => [
        'Haram Travels' => env('INVOICE_TERMS_LEGAL_NAME', 'Haram Travel'),
        'Al Kabir Travel' => env('INVOICE_AL_KABIR_TERMS_LEGAL_NAME', 'GM Tours & Travels T/A Al Kabir Travel'),
    ],

    /*
    | Company-specific terms & conditions Blade views.
    | Keys must match the company name on the folder (case-insensitive).
    | Unknown companies fall back to Haram Travels.
    */
    'terms_views' => [
        'Haram Travels' => 'invoices.partials.terms-and-conditions-haram-travels',
        'Al Kabir Travel' => 'invoices.partials.terms-and-conditions-al-kabir-travel',
    ],

    /*
    | Company-specific payment instructions shown on invoices after the ziarat section.
    | Keys must match the company name on the folder (case-insensitive).
    */
    'company_payment_sections' => [
        'Haram Travels' => [
            'intro' => [
                'For your security, please ensure all payments are made only to the company bank account listed Below.',
                'Haram Travel will not accept responsibility for payments made to any personal bank account or third party unless officially confirmed in writing by the company.',
            ],
            'bank_details' => [
                ['label' => 'Bank Name', 'value' => 'HSBC'],
                ['label' => 'Account Name', 'value' => 'Bukhari Travel Limited'],
                ['label' => 'Sort Code', 'value' => '40-11-56'],
                ['label' => 'Account Number', 'value' => '01114646'],
            ],
        ],
        'Al Kabir Travel' => [
            'intro' => [
                'For your security, please ensure all payments are made only to the company bank account listed Below.',
                'GM Tours & Travel T/A Al Kabir Travel will not accept responsibility for payments made to any personal bank account or third party unless officially confirmed in writing by the company.',
            ],
            'bank_details' => [
                ['label' => 'Bank Name', 'value' => 'U.S. Bank'],
                ['label' => 'Account Name', 'value' => 'GM Tours & Travel'],
                ['label' => 'Account Number', 'value' => '199389318361'],
                ['label' => 'Routing Number', 'value' => '071904779'],
                ['label' => 'Zelle Email', 'value' => 'Gmpayments786@gmail.com'],
            ],
        ],
    ],
];
