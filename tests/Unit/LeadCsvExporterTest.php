<?php

namespace Tests\Unit;

use App\Models\Lead;
use App\Services\LeadCsvExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadCsvExporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_headings_exclude_source_column(): void
    {
        $headings = app(LeadCsvExporter::class)->headings(LeadCsvExporter::CONTEXT_ADMIN);

        $this->assertSame([
            'Agent',
            'Customer Name',
            'Phone Number',
            'Email',
            'Company Name',
            'City',
            'Passengers',
            'Status',
        ], $headings);

        $this->assertNotContains('Source', $headings);
    }

    public function test_admin_row_excludes_source_value(): void
    {
        $lead = Lead::query()->create([
            'customer_name' => 'Export Customer',
            'phone_number' => '03001234567',
            'email' => 'export@example.com',
            'city' => 'Lahore',
            'total_passengers' => 4,
            'source' => 'website',
            'status' => Lead::STATUS_NEW,
            'agent_name' => 'Agent One',
        ]);

        $row = app(LeadCsvExporter::class)->row($lead, LeadCsvExporter::CONTEXT_ADMIN);

        $this->assertSame([
            'Agent One',
            'Export Customer',
            '03001234567',
            'export@example.com',
            '',
            'Lahore',
            '4',
            'New',
        ], $row);

        $this->assertNotContains('website', $row);
    }
}
