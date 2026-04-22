<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'city',
        'source',
        'notes',
        'order_type',
        'vendor_reference',
        'company_id',
        'status',
        'destination_id',
        'travel_date',
        'balance_due_date',
        'flight_itinerary',
        'ziarat_makkah',
        'ziarat_madinah',
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
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /**
     * Tailwind pill class suffix (matches `.concierge-pill-*` in app.css).
     */
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

    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
            'balance_due_date' => 'date',
            'ziarat_makkah' => 'boolean',
            'ziarat_madinah' => 'boolean',
        ];
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

    /**
     * @return HasMany<LeadItinerary, $this>
     */
    public function itineraries(): HasMany
    {
        return $this->hasMany(LeadItinerary::class);
    }

    /**
     * @return HasMany<LeadPassenger, $this>
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(LeadPassenger::class);
    }

    /**
     * @return HasMany<LeadPackageCost, $this>
     */
    public function packageCosts(): HasMany
    {
        return $this->hasMany(LeadPackageCost::class);
    }
}
