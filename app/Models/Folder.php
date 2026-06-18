<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    public const UPCOMING_TRAVEL_DATE_WINDOW_DAYS = 20;

    /**
     * Allowed order types for folders (delegates to {@see folder_order_types()}).
     *
     * @return list<string>
     */
    public static function orderTypes(): array
    {
        return folder_order_types();
    }

    /**
     * Folders whose {@see Folder::$travel_date} falls between today and today + $daysFromToday (inclusive).
     *
     * @param  Builder<Folder>  $query
     * @return Builder<Folder>
     */
    public function scopeUpcomingByTravelDate(Builder $query, int $daysFromToday = self::UPCOMING_TRAVEL_DATE_WINDOW_DAYS): Builder
    {
        $from = now()->startOfDay();
        $to = now()->addDays($daysFromToday)->startOfDay();

        return $query
            ->whereNotNull('travel_date')
            ->whereDate('travel_date', '>=', $from)
            ->whereDate('travel_date', '<=', $to);
    }

    public static function countUpcomingByTravelDate(?int $agentId = null, int $daysFromToday = self::UPCOMING_TRAVEL_DATE_WINDOW_DAYS): int
    {
        $query = static::query()->upcomingByTravelDate($daysFromToday);

        if ($agentId !== null) {
            $query->where('agent_id', $agentId);
        }

        return $query->count();
    }

    /**
     * Adds {@see Folder::$is_incomplete_booking} (exists) for list row styling without loading hotel rows.
     *
     * @param  Builder<Folder>  $query
     * @return Builder<Folder>
     */
    public function scopeWithIncompleteBookingFlag(Builder $query): Builder
    {
        return $query->withExists([
            'hotelDetails as is_incomplete_booking' => fn (Builder $hotelQuery) => $hotelQuery->where('status', 'issue_later'),
        ]);
    }

    /**
     * Incomplete bookings first. Use after {@see scopeWithIncompleteBookingFlag}.
     *
     * @param  Builder<Folder>  $query
     * @return Builder<Folder>
     */
    public function scopeOrderByIncompleteBookingFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_incomplete_booking');
    }

    protected static function booted(): void
    {
        static::saving(function (Folder $folder): void {
            if ($folder->isDirty('agent_id')) {
                folder_sync_agent_name_from_user($folder);
            }
        });
    }

    protected $fillable = [
        'agent_id',
        'agent_name',
        'order_type',
        'vendor_reference',
        'customer_name',
        'company_id',
        'destination_id',
        'travel_date',
        'booking_date',
        'balance_due_date',
        'makkah_ziarat',
        'madinah_ziarat',
        'lock',
    ];

    protected $appends = [
        'status',
    ];

    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
            'booking_date' => 'date',
            'balance_due_date' => 'date',
            'makkah_ziarat' => 'boolean',
            'madinah_ziarat' => 'boolean',
            'lock' => 'boolean',
        ];
    }

    public function isLocked(): bool
    {
        return (bool) $this->lock;
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

    /**
     * @return HasMany<FolderItinerary, $this>
     */
    public function itineraries(): HasMany
    {
        return $this->hasMany(FolderItinerary::class);
    }

    /**
     * @return HasMany<FolderPassenger, $this>
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(FolderPassenger::class);
    }

    /**
     * @return HasMany<FolderPackageCost, $this>
     */
    public function packageCosts(): HasMany
    {
        return $this->hasMany(FolderPackageCost::class);
    }

    /**
     * @return HasMany<FolderHotelDetail, $this>
     */
    public function hotelDetails(): HasMany
    {
        return $this->hasMany(FolderHotelDetail::class);
    }

    /**
     * @return HasMany<FolderTransportDetail, $this>
     */
    public function transportDetails(): HasMany
    {
        return $this->hasMany(FolderTransportDetail::class);
    }

    /**
     * @return HasMany<FolderVisaDetail, $this>
     */
    public function visaDetails(): HasMany
    {
        return $this->hasMany(FolderVisaDetail::class);
    }

    /**
     * @return HasMany<FolderOtherDetail, $this>
     */
    public function otherDetails(): HasMany
    {
        return $this->hasMany(FolderOtherDetail::class);
    }

    /**
     * @return HasMany<FolderPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(FolderPayment::class)
            ->orderByDesc('payment_date')
            ->orderByDesc('id');
    }

    /**
     * Aggregated totals aligned with the folder form cost summary (package sell / total cost, section costs, margin).
     *
     * @return array{total_sale: float, flight_cost: float, hotel_cost: float, transport_cost: float, visa_cost: float, others_cost: float, margin: float}
     */
    public function costSummary(): array
    {
        $totalSale = (float) $this->packageCosts->sum(fn ($c) => (float) ($c->sell ?? 0));
        $flightCost = (float) $this->packageCosts->sum(fn ($c) => (float) ($c->total_cost ?? 0));
        $hotelCost = (float) $this->hotelDetails->sum(fn ($h) => (float) ($h->cost ?? 0));
        $transportCost = (float) $this->transportDetails->sum(fn ($t) => (float) ($t->cost ?? 0));
        $visaCost = (float) $this->visaDetails->sum(fn ($v) => (float) ($v->cost ?? 0));
        $othersCost = (float) $this->otherDetails->sum(fn ($o) => (float) ($o->cost ?? 0));
        $margin = $totalSale - $flightCost - $hotelCost - $transportCost - $visaCost - $othersCost;

        return [
            'total_sale' => $totalSale,
            'flight_cost' => $flightCost,
            'hotel_cost' => $hotelCost,
            'transport_cost' => $transportCost,
            'visa_cost' => $visaCost,
            'others_cost' => $othersCost,
            'margin' => $margin,
        ];
    }

    public function getStatusAttribute(): string
    {
        return $this->hotelDetails->some(fn ($h) => $h->status === 'issue_later') ? 'Incomplete' : 'Successful';
    }
}
