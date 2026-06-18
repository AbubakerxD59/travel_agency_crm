<?php

namespace Tests\Unit;

use App\Models\Lead;
use App\Services\LeadDuplicateChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadDuplicateCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_lead_with_matching_email_and_phone(): void
    {
        $existing = Lead::query()->create([
            'customer_name' => 'Existing Customer',
            'email' => 'Customer@Example.com',
            'phone_number' => '03001234567',
            'status' => Lead::STATUS_NEW,
        ]);

        $duplicate = app(LeadDuplicateChecker::class)->find('customer@example.com', '03001234567');

        $this->assertNotNull($duplicate);
        $this->assertTrue($duplicate->is($existing));
    }

    public function test_it_finds_lead_when_both_emails_are_empty(): void
    {
        $existing = Lead::query()->create([
            'customer_name' => 'No Email Customer',
            'email' => null,
            'phone_number' => '03007654321',
            'status' => Lead::STATUS_NEW,
        ]);

        $duplicate = app(LeadDuplicateChecker::class)->find(null, '03007654321');

        $this->assertNotNull($duplicate);
        $this->assertTrue($duplicate->is($existing));
    }

    public function test_it_does_not_match_when_only_phone_matches(): void
    {
        Lead::query()->create([
            'customer_name' => 'First Customer',
            'email' => 'first@example.com',
            'phone_number' => '03001111111',
            'status' => Lead::STATUS_NEW,
        ]);

        $duplicate = app(LeadDuplicateChecker::class)->find('second@example.com', '03001111111');

        $this->assertNull($duplicate);
    }

    public function test_it_excludes_current_lead_when_updating(): void
    {
        $existing = Lead::query()->create([
            'customer_name' => 'Same Customer',
            'email' => 'same@example.com',
            'phone_number' => '03002222222',
            'status' => Lead::STATUS_NEW,
        ]);

        $duplicate = app(LeadDuplicateChecker::class)->find('same@example.com', '03002222222', $existing->id);

        $this->assertNull($duplicate);
    }
}
