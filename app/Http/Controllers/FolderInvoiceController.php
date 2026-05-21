<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\FolderInvoiceViewData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FolderInvoiceController extends Controller
{
    public function __construct(
        private readonly FolderInvoiceViewData $invoiceViewData,
    ) {}

    public function show(Request $request, Folder $folder): View
    {
        $this->authorizeFolderInvoice($request, $folder);

        return view('invoices.travel', $this->invoiceViewData->build($folder));
    }

    private function authorizeFolderInvoice(Request $request, Folder $folder): void
    {
        $user = $request->user();

        if ($user === null || ! $user->can('folders.access') || $user->hasRole('agent')) {
            abort(403);
        }
    }
}
