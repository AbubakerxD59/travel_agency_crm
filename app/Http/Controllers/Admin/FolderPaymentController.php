<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFolderPaymentImageRequest;
use App\Models\FolderPayment;
use App\Support\FolderPaymentImageStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class FolderPaymentController extends Controller
{
    public function __construct(
        private readonly FolderPaymentImageStorage $paymentImages,
    ) {}

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

    public function show(FolderPayment $folderPayment): View
    {
        $folderPayment->load(['folder.agent', 'folder.company', 'bank']);

        return view('admin.folder-payments.show', [
            'payment' => $folderPayment,
            'canEditImage' => ! $folderPayment->isLocked(),
        ]);
    }

    public function updateImage(UpdateFolderPaymentImageRequest $request, FolderPayment $folderPayment): RedirectResponse
    {
        if ($folderPayment->isLocked()) {
            return redirect()
                ->route('admin.folder-payments.show', $folderPayment)
                ->with('error', __('This payment is locked and cannot be changed.'));
        }

        try {
            $previousPath = $folderPayment->image;
            $path = $this->paymentImages->store($request->file('image'));

            if ($path === null) {
                return redirect()
                    ->route('admin.folder-payments.show', $folderPayment)
                    ->with('error', __('Could not upload image. Please try again.'));
            }

            $folderPayment->update(['image' => $path]);
            $this->paymentImages->delete($previousPath);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.folder-payments.show', $folderPayment)
                ->with('error', __('Could not upload image. Please try again.'));
        }

        return redirect()
            ->route('admin.folder-payments.show', $folderPayment)
            ->with('status', __('Payment image saved.'));
    }

    public function destroyImage(FolderPayment $folderPayment): RedirectResponse
    {
        if ($folderPayment->isLocked()) {
            return redirect()
                ->route('admin.folder-payments.show', $folderPayment)
                ->with('error', __('This payment is locked and cannot be changed.'));
        }

        $folderPayment->deleteStoredImage();
        $folderPayment->update(['image' => null]);

        return redirect()
            ->route('admin.folder-payments.show', $folderPayment)
            ->with('status', __('Payment image removed.'));
    }

    public function approve(FolderPayment $folderPayment): RedirectResponse
    {
        if ($folderPayment->isLocked()) {
            return redirect()
                ->back()
                ->with('error', __('This payment is locked and cannot be changed.'));
        }

        if ($folderPayment->approval_status !== FolderPayment::STATUS_PENDING) {
            return redirect()
                ->back()
                ->with('error', __('This payment is not awaiting approval.'));
        }

        $folderPayment->update([
            'approval_status' => FolderPayment::STATUS_APPROVED,
            'locked_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('status', __('Payment approved and locked.'));
    }

    public function reject(FolderPayment $folderPayment): RedirectResponse
    {
        if ($folderPayment->isLocked()) {
            return redirect()
                ->back()
                ->with('error', __('This payment is locked and cannot be changed.'));
        }

        if ($folderPayment->approval_status !== FolderPayment::STATUS_PENDING) {
            return redirect()
                ->back()
                ->with('error', __('This payment is not awaiting approval.'));
        }

        $folderPayment->update([
            'approval_status' => FolderPayment::STATUS_REJECTED,
            'locked_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('status', __('Payment rejected and locked.'));
    }
}
