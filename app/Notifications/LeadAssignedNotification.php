<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Lead $lead,
        private readonly bool $isReassigned = false,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->lead->loadMissing(['company']);

        $customerName = $this->lead->customer_name ?: 'Customer';
        $subject = $this->isReassigned
            ? "Lead reassigned: {$customerName}"
            : "New lead assigned: {$customerName}";

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.lead-assigned', [
                'lead' => $this->lead,
                'agent' => $notifiable,
                'isReassigned' => $this->isReassigned,
                'leadUrl' => route('agent.leads.show', $this->lead),
            ]);
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
