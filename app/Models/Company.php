<?php

namespace App\Models;

use App\Support\CompanyImageStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'country_id',
        'image',
        'website_link',
    ];

    protected $companies = [
        "Al Kabir Travel",
        "Haram Travels"
    ];

    protected static function booted(): void
    {
        static::deleting(function (Company $company): void {
            $company->deleteStoredImage();
        });
    }

    public function imageUrl(): ?string
    {
        return app(CompanyImageStorage::class)->url($this->image);
    }

    public function deleteStoredImage(): void
    {
        app(CompanyImageStorage::class)->delete($this->image);
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
