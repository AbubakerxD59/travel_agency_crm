<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FolderPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FolderPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $folderId = $request->integer('folder_id') ?: null;

        $payments = FolderPayment::query()
            ->with(['folder.agent', 'bank'])
            ->when($folderId !== null, fn ($q) => $q->where('folder_id', $folderId))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.folder-payments.index', [
            'payments' => $payments,
            'filterFolderId' => $folderId,
        ]);
    }

    public function approve(FolderPayment $folderPayment): RedirectResponse
    {
        if ($folderPayment->approval_status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', __('This payment is not awaiting approval.'));
        }

        $folderPayment->update(['approval_status' => 'approved']);

        return redirect()
            ->back()
            ->with('status', __('Payment approved.'));
    }

    public function reject(FolderPayment $folderPayment): RedirectResponse
    {
        if ($folderPayment->approval_status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', __('This payment is not awaiting approval.'));
        }

        $folderPayment->update(['approval_status' => 'rejected']);

        return redirect()
            ->back()
            ->with('status', __('Payment rejected.'));
    }
}
