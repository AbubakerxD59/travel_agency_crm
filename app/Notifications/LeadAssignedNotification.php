<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Lead $lead,
        private readonly bool $isReassigned = false,
    ) {
    }

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
        $customerName = $this->lead->customer_name ?: 'Customer';

        return [
            'lead_id' => $this->lead->id,
            'agent_id' => $this->lead->agent_id,
            'customer_name' => $this->lead->customer_name,
            'status' => $this->lead->status,
            'type' => $this->isReassigned ? 'lead_reassigned' : 'lead_assigned',
            'title' => $this->isReassigned ? 'Lead reassigned' : 'New lead assigned',
            'message' => $this->isReassigned
                ? "Lead for {$customerName} has been reassigned to you."
                : "A new lead for {$customerName} has been assigned to you.",
            'url' => route('agent.leads.show', $this->lead),
        ];
    }
}
