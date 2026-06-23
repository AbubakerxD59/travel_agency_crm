<?php

namespace App\Services;

use App\Models\Folder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class FolderInvoicePdf
{
    public function __construct(
        private readonly FolderInvoiceViewData $invoiceViewData,
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

        $filename = $this->filename($data['invoice_number'] ?? (string) $folder->id);

        return response($invoicePdf, 200, [
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
