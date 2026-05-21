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
    'terms_legal_name' => env('INVOICE_TERMS_LEGAL_NAME', 'Travigence Ltd'),

];
