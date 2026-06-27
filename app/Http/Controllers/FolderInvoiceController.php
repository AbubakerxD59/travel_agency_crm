<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\FolderInvoicePdf;
use App\Services\FolderInvoiceViewData;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FolderInvoiceController extends Controller
{
    public function __construct(
        private readonly FolderInvoicePdf $invoicePdf,
        private readonly FolderInvoiceViewData $invoiceViewData,
    ) {}

    public function show(Request $request, Folder $folder): View
    {
        $this->authorizeFolderInvoice($request, $folder);

        $data = $this->invoiceViewData->build($folder);
        $data['pdf_download_url'] = route($this->downloadRouteName($request), $folder);

        return view('invoices.travel', $data);
    }

    public function download(Request $request, Folder $folder): Response
    {
        $this->authorizeFolderInvoice($request, $folder);

        return $this->invoicePdf->download($folder);
    }

    private function downloadRouteName(Request $request): string
    {
        return $request->routeIs('agent.*')
            ? 'agent.folders.invoice.download'
            : 'admin.folders.invoice.download';
    }

    private function authorizeFolderInvoice(Request $request, Folder $folder): void
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
