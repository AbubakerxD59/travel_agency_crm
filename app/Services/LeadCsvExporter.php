<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadCsvExporter
{
    public const CONTEXT_ADMIN = 'admin';

    public const CONTEXT_AGENT = 'agent';

    /**
     * @param  Collection<int, Lead>  $leads
     */
    public function download(Collection $leads, string $context): StreamedResponse
    {
        $filename = 'leads-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($leads, $context): void {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fputcsv($handle, $this->headings($context));

            foreach ($leads as $lead) {
                fputcsv($handle, $this->row($lead, $context));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return list<string>
     */
    public function headings(string $context): array
    {
        return match ($context) {
            self::CONTEXT_ADMIN => [
                'Agent',
                'Customer Name',
                'Phone Number',
                'Email',
                'Company Name',
                'City',
                'Passengers',
                'Status',
            ],
            self::CONTEXT_AGENT => [
                'Customer Name',
                'Phone Number',
                'Company Name',
                'City',
                'No. of Passengers',
                'Status',
            ],
            default => [],
        };
    }

    /**
     * @return list<int|string|null>
     */
    public function row(Lead $lead, string $context): array
    {
        return match ($context) {
            self::CONTEXT_ADMIN => [
                lead_agent_display_name($lead),
                $lead->customer_name ?? '',
                $lead->phone_number ?? '',
                $lead->email ?? '',
                $lead->company?->name ?? '',
                $lead->city ?? '',
                $lead->total_passengers !== null ? (string) $lead->total_passengers : '',
                $lead->statusLabel(),
            ],
            self::CONTEXT_AGENT => [
                $lead->customer_name ?? '',
                $lead->phone_number ?? '',
                $lead->company?->name ?? '',
                $lead->city ?? '',
                $lead->total_passengers !== null ? (string) $lead->total_passengers : '',
                $lead->statusLabel(),
            ],
            default => [],
        };
    }
}
