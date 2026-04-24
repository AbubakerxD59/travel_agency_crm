<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_FOLLOW_UP = 'follow_up';

    public const STATUS_SALE_DONE = 'sale_done';

    public const STATUS_NOT_CONVERTED = 'not_converted';

    protected $fillable = [
        'agent_id',
        'customer_name',
        'phone_number',
        'email',
        'company_id',
        'city',
        'source',
        'notes',
        'status'
    ];

    /**
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_FOLLOW_UP => 'Follow-up',
            self::STATUS_SALE_DONE => 'Sale done',
            self::STATUS_NOT_CONVERTED => 'Not converted',
        ];
    }

    /**
     * @return list<string>
     */
    public static function statusKeys(): array
    {
        return array_keys(self::statusLabels());
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? (string) ($this->status ?? '');
    }

    public function statusPillClass(): string
    {
        return match ($this->status) {
            self::STATUS_NEW => 'new',
            self::STATUS_CONTACTED => 'contacted',
            self::STATUS_FOLLOW_UP => 'follow-up',
            self::STATUS_SALE_DONE => 'sale-done',
            self::STATUS_NOT_CONVERTED => 'not-converted',
            default => 'meta',
        };
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Destination, $this>
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }
}
