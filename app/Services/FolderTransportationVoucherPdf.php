<?php

namespace App\Services;

use App\Models\Folder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class FolderTransportationVoucherPdf
{
    public function __construct(
        private readonly FolderTransportationVoucherViewData $voucherViewData,
    ) {}

    public function download(Folder $folder): Response
    {
        $data = $this->voucherViewData->build($folder);
        $data['for_pdf'] = true;

        $pdf = Pdf::loadView('transportation-vouchers.voucher', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'times')
            ->output();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->filename($folder).'"',
        ]);
    }

    private function filename(Folder $folder): string
    {
        $ref = trim((string) ($folder->vendor_reference ?: $folder->id));
        $safe = trim((string) preg_replace('/[^\w\-]+/', '-', $ref), '-');

        return 'Transportation-Voucher-'.($safe !== '' ? $safe : 'folder').'.pdf';
    }
}
