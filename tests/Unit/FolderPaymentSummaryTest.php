<?php

namespace Tests\Unit;

use App\Models\Folder;
use App\Models\FolderPackageCost;
use App\Models\FolderPayment;
use Tests\TestCase;

class FolderPaymentSummaryTest extends TestCase
{
    public function test_remaining_amount_deducts_only_approved_payments(): void
    {
        $folder = $this->folderWithSaleAndPayments(1000.0, [
            ['amount' => 250.5, 'approval_status' => FolderPayment::STATUS_APPROVED],
            ['amount' => 100.0, 'approval_status' => FolderPayment::STATUS_PENDING],
            ['amount' => 50.0, 'approval_status' => FolderPayment::STATUS_REJECTED],
        ]);

        $summary = $folder->paymentSummary();

        $this->assertSame(1000.0, $summary['total_sale']);
        $this->assertSame(250.5, $summary['amount_paid']);
        $this->assertSame(749.5, $summary['remaining_amount']);
        $this->assertCount(1, $summary['approved_payments']);
    }

    public function test_remaining_amount_is_zero_when_approved_payments_cover_total_sale(): void
    {
        $folder = $this->folderWithSaleAndPayments(500.0, [
            ['amount' => 300.0, 'approval_status' => FolderPayment::STATUS_APPROVED],
            ['amount' => 250.0, 'approval_status' => FolderPayment::STATUS_APPROVED],
        ]);

        $summary = $folder->paymentSummary();

        $this->assertSame(550.0, $summary['amount_paid']);
        $this->assertSame(0.0, $summary['remaining_amount']);
        $this->assertCount(2, $summary['approved_payments']);
    }

    /**
     * @param  list<array{amount: float, approval_status: string}>  $payments
     */
    private function folderWithSaleAndPayments(float $totalSale, array $payments): Folder
    {
        $folder = new Folder;
        $folder->setRelation('packageCosts', collect([
            new FolderPackageCost(['sell' => $totalSale, 'total_cost' => 0]),
        ]));
        $folder->setRelation('hotelDetails', collect());
        $folder->setRelation('transportDetails', collect());
        $folder->setRelation('visaDetails', collect());
        $folder->setRelation('otherDetails', collect());
        $folder->setRelation('payments', collect(array_map(
            fn (array $payment): FolderPayment => new FolderPayment($payment),
            $payments,
        )));

        return $folder;
    }
}
