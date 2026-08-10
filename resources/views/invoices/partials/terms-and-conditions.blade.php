{{--
    Picks company-specific invoice terms. Default: Haram Travels.
--}}
@include(invoice_terms_and_conditions_view($company['name'] ?? null))
