{{--
    Invoice terms & conditions are sourced from the PDF in public/.
    For browser preview, the PDF is embedded below. For generated invoice PDFs,
    the pages are appended after the invoice body to preserve original styling.
--}}
@if (empty($for_pdf))
    <section class="invoice-terms-pdf">
        <object data="{{ invoice_terms_and_conditions_pdf_url() }}#toolbar=0&navpanes=0"
            type="application/pdf"
            class="invoice-terms-pdf__embed"
            aria-label="Terms and Conditions">
            <p class="invoice-terms-pdf__fallback">
                <a href="{{ invoice_terms_and_conditions_pdf_url() }}" target="_blank" rel="noopener noreferrer">
                    View Terms &amp; Conditions (PDF)
                </a>
            </p>
        </object>
    </section>
@endif
