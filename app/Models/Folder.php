<?php

namespace App\Models;

use App\Models\FolderHotelDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    protected $fillable = [
        'agent_id',
        'order_type',
        'vendor_reference',
        'company_id',
        'destination_id',
        'travel_date',
        'balance_due_date',
        'makkah_ziarat',
        'madinah_ziarat',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'travel_date' => 'date',
            'balance_due_date' => 'date',
            'makkah_ziarat' => 'boolean',
            'madinah_ziarat' => 'boolean',
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
}
