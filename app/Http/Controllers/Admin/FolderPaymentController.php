<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFolderPaymentImageRequest;
use App\Models\FolderPayment;
use App\Models\User;
use App\Support\FolderPaymentImageStorage;
use Illuminate\Database\Eloquent\Builder;
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
        $params = $this->paymentFilterParams($request);

        $payments = FolderPayment::query()
            ->with(['folder.agent', 'bank']);
        $this->applyPaymentListFilters($payments, $params);
        $payments = $payments
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.folder-payments.index', [
            'payments' => $payments,
            'filterFolderId' => $params['folderId'],
            'search' => $params['search'],
            'selectedAgentId' => $params['agentId'],
            'selectedPaymentDate' => $params['paymentDate'],
            'selectedStatus' => $params['status'],
            'agents' => User::role('agent')->orderBy('name')->get(['id', 'name']),
            'statuses' => [
                FolderPayment::STATUS_PENDING => 'Pending',
                FolderPayment::STATUS_APPROVED => 'Approved',
                FolderPayment::STATUS_REJECTED => 'Rejected',
            ],
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

    /**
     * @return array{
     *     folderId: ?int,
     *     search: string,
     *     agentId: ?int,
     *     paymentDate: string,
     *     status: string
     * }
     */
    private function paymentFilterParams(Request $request): array
    {
        $folderId = $request->integer('folder_id') ?: null;
        $search = trim((string) $request->string('search')->value());
        $agentId = $request->integer('agent_id') ?: null;
        $paymentDate = trim((string) $request->query('payment_date', ''));
        $status = trim((string) $request->query('status', ''));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDate)) {
            $paymentDate = '';
        }

        $validStatuses = [
            FolderPayment::STATUS_PENDING,
            FolderPayment::STATUS_APPROVED,
            FolderPayment::STATUS_REJECTED,
        ];

        if ($status !== '' && ! in_array($status, $validStatuses, true)) {
            $status = '';
        }

        return [
            'folderId' => $folderId,
            'search' => $search,
            'agentId' => $agentId,
            'paymentDate' => $paymentDate,
            'status' => $status,
        ];
    }

    /**
     * @param  array{
     *     folderId: ?int,
     *     search: string,
     *     agentId: ?int,
     *     paymentDate: string,
     *     status: string
     * }  $params
     */
    private function applyPaymentListFilters(Builder $query, array $params): void
    {
        $query
            ->when($params['folderId'] !== null, fn ($q) => $q->where('folder_id', $params['folderId']))
            ->when($params['agentId'] !== null, fn ($q) => $q->whereHas(
                'folder',
                fn ($folderQuery) => $folderQuery->where('agent_id', $params['agentId']),
            ))
            ->when($params['paymentDate'] !== '', fn ($q) => $q->whereDate('payment_date', $params['paymentDate']))
            ->when($params['status'] !== '', fn ($q) => $q->where('approval_status', $params['status']))
            ->when($params['search'] !== '', function ($q) use ($params) {
                $q->where(function ($searchQuery) use ($params) {
                    $searchQuery
                        ->where('reference_no', 'like', '%'.$params['search'].'%')
                        ->orWhereHas(
                            'folder',
                            fn ($folderQuery) => $folderQuery->where('customer_name', 'like', '%'.$params['search'].'%'),
                        );
                });
            });
    }
}
