<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class AgentWebPushService
{
    public function isConfigured(): bool
    {
        $publicKey = config('webpush.vapid.public_key');
        $privateKey = config('webpush.vapid.private_key');

        return is_string($publicKey) && $publicKey !== ''
            && is_string($privateKey) && $privateKey !== '';
    }

    public function sendLeadAssigned(User $agent, Lead $lead, bool $isReassigned = false): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $customerName = $lead->customer_name ?: 'Customer';
        $title = $isReassigned ? 'Lead reassigned' : 'New lead assigned';
        $body = $isReassigned
            ? "Lead for {$customerName} has been reassigned to you."
            : "A new lead for {$customerName} has been assigned to you.";

        $this->sendToUser($agent, [
            'title' => $title,
            'body' => $body,
            'url' => route('agent.leads.show', $lead),
            'tag' => 'lead-assigned-'.$lead->id,
            'type' => $isReassigned ? 'lead_reassigned' : 'lead_assigned',
            'lead_id' => $lead->id,
        ]);
    }

    /**
     * @param  array{title: string, body: string, url?: string, tag?: string, type?: string, lead_id?: int}  $payload
     */
    public function sendToUser(User $user, array $payload): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $subscriptions = $user->pushSubscriptions()->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => (string) config('webpush.vapid.subject'),
                    'publicKey' => (string) config('webpush.vapid.public_key'),
                    'privateKey' => (string) config('webpush.vapid.private_key'),
                ],
            ]);

            $jsonPayload = json_encode($payload);

            foreach ($subscriptions as $stored) {
                $subscription = Subscription::create([
                    'endpoint' => $stored->endpoint,
                    'publicKey' => $stored->public_key,
                    'authToken' => $stored->auth_token,
                    'contentEncoding' => $stored->content_encoding ?: 'aesgcm',
                ]);

                $webPush->queueNotification($subscription, $jsonPayload, [
                    'TTL' => 300,
                    'urgency' => 'high',
                ]);
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    continue;
                }

                $endpoint = $report->getRequest()->getUri()->__toString();
                PushSubscription::query()->where('endpoint', $endpoint)->delete();

                Log::warning('Web push delivery failed', [
                    'reason' => $report->getReason(),
                    'endpoint' => $endpoint,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error('Web push send failed', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
