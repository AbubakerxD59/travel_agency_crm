<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\FolderTransportationVoucherPdf;
use App\Services\FolderTransportationVoucherViewData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FolderTransportationVoucherController extends Controller
{
    public function __construct(
        private readonly FolderTransportationVoucherPdf $voucherPdf,
        private readonly FolderTransportationVoucherViewData $voucherViewData,
    ) {}

    public function show(Request $request, Folder $folder): View
    {
        $this->authorizeVoucher($request, $folder);

        $data = $this->voucherViewData->build($folder);

        $data['pdf_download_url'] = route($this->downloadRouteName($request), $folder);

        return view('transportation-vouchers.voucher', $data);
    }

    public function download(Request $request, Folder $folder): Response
    {
        $this->authorizeVoucher($request, $folder);

        return $this->voucherPdf->download($folder);
    }

    private function downloadRouteName(Request $request): string
    {
        return $request->routeIs('agent.*')
            ? 'agent.folders.transportation-voucher.download'
            : 'admin.folders.transportation-voucher.download';
    }

    private function authorizeVoucher(Request $request, Folder $folder): void
    {
        $user = $request->user();

        if ($user === null || ! $user->can('folders.access')) {
            abort(403);
        }

        if ($user->hasRole('agent') && (int) $folder->agent_id !== (int) $user->id) {
            abort(403);
        }
    }
}
