<?php

namespace App\Models;

use App\Support\FolderPaymentImageStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderPayment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'folder_id',
        'amount',
        'reference_no',
        'payment_date',
        'mode_of_payment',
        'bank_id',
        'image',
        'approval_status',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'locked_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (FolderPayment $payment): void {
            if ($payment->getOriginal('locked_at') !== null) {
                throw new \RuntimeException('Locked folder payments cannot be modified.');
            }
        });

        static::deleting(function (FolderPayment $payment): bool {
            if ($payment->locked_at !== null) {
                return false;
            }

            $payment->deleteStoredImage();

            return true;
        });
    }

    public function imageUrl(): ?string
    {
        return app(FolderPaymentImageStorage::class)->url($this->image);
    }

    public function deleteStoredImage(): void
    {
        app(FolderPaymentImageStorage::class)->delete($this->image);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * @return BelongsTo<Folder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * @return BelongsTo<Bank, $this>
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }
}
