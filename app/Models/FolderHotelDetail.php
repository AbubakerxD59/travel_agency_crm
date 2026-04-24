<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderHotelDetail extends Model
{
    protected $fillable = [
        'folder_id',
        'sr_no',
        'supplier',
        'hotel_name',
        'guest_name',
        'rooms',
        'type',
        'meals',
        'date_in',
        'date_out',
        'nights',
        'supplier_ref',
        'cost',
        'margin',
        'sell',
        'hotel_city',
    ];

    protected function casts(): array
    {
        return [
            'date_in' => 'date',
            'date_out' => 'date',
            'cost' => 'decimal:2',
            'margin' => 'decimal:2',
            'sell' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Folder, $this>
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }
}
