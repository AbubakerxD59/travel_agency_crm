<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Services\FolderInvoicePdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FolderInvoiceController extends Controller
{
    public function __construct(
        private readonly FolderInvoicePdf $invoicePdf,
    ) {}

    public function show(Request $request, Folder $folder): Response
    {
        $this->authorizeFolderInvoice($request, $folder);

        return $this->invoicePdf->download($folder);
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
