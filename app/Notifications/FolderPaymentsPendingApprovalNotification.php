<?php

namespace App\Notifications;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FolderPaymentsPendingApprovalNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Folder $folder,
        private readonly User $agent,
        private readonly int $paymentCount,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $customer = $this->folder->customer_name ?: __('Customer');
        $agentName = $this->agent->name;
        $n = $this->paymentCount;
        $message = $n === 1
            ? __(':agent added 1 payment to folder #:id (:customer).', [
                'agent' => $agentName,
                'id' => $this->folder->id,
                'customer' => $customer,
            ])
            : __(':agent added :count payments to folder #:id (:customer).', [
                'agent' => $agentName,
                'count' => $n,
                'id' => $this->folder->id,
                'customer' => $customer,
            ]);

        return [
            'folder_id' => $this->folder->id,
            'agent_id' => $this->agent->id,
            'customer_name' => $this->folder->customer_name,
            'type' => 'folder_payment_pending',
            'title' => __('Payments pending approval'),
            'message' => $message,
            'url' => route('admin.folder-payments.index', ['folder_id' => $this->folder->id]),
        ];
    }
}
