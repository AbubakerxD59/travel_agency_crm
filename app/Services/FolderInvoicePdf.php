<?php

namespace App\Services;

use App\Models\Folder;
use App\Support\InvoicePdfMerger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use RuntimeException;

class FolderInvoicePdf
{
    public function __construct(
        private readonly FolderInvoiceViewData $invoiceViewData,
        private readonly InvoicePdfMerger $pdfMerger,
    ) {}

    public function download(Folder $folder): Response
    {
        $data = $this->invoiceViewData->build($folder);
        $data['for_pdf'] = true;

        $invoicePdf = Pdf::loadView('invoices.travel', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'times')
            ->output();

        $termsPdfPath = invoice_terms_and_conditions_pdf_path();

        if (! is_file($termsPdfPath)) {
            throw new RuntimeException('Invoice terms and conditions PDF is missing from the public folder.');
        }

        $mergedPdf = $this->pdfMerger->merge([$invoicePdf, $termsPdfPath]);

        $filename = $this->filename($data['invoice_number'] ?? (string) $folder->id);

        return response($mergedPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function filename(string $invoiceNumber): string
    {
        $safe = trim((string) preg_replace('/[^\w\-]+/', '-', $invoiceNumber), '-');

        return 'Invoice-'.($safe !== '' ? $safe : 'folder').'.pdf';
    }
}
