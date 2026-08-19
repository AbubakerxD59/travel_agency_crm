<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    public const STATUS_NEW = 'new';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_FOLLOW_UP = 'follow_up';

    public const STATUS_SALE_DONE = 'sale_done';

    public const STATUS_NOT_CONVERTED = 'not_converted';

    public const STATUS_NO_INITIAL_RESPONSE = 'no_initial_response';

    protected $fillable = [
        'agent_id',
        'agent_name',
        'lead_assign_date',
        'customer_name',
        'phone_number',
        'email',
        'company_id',
        'city',
        'total_passengers',
        'source',
        'notes',
        'status',
        'not_converted_reason',
    ];

    protected static function booted(): void
    {
        static::saving(function (Lead $lead): void {
            if ($lead->isDirty('agent_id')) {
                lead_sync_agent_name_from_user($lead);
            }
        });
    }

    protected $casts = [
        'lead_assign_date' => 'datetime',
        'total_passengers' => 'integer',
        'deleted_at' => 'datetime',
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
            self::STATUS_NO_INITIAL_RESPONSE => 'No Initial Response',
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
            self::STATUS_NO_INITIAL_RESPONSE => 'no-initial-response',
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
        return $this->belongsTo(User::class, 'agent_id')->withTrashed();
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
