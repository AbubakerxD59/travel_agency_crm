<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderTransportDetail extends Model
{
    protected $fillable = [
        'folder_id',
        'supplier',
        'description',
        'origin',
        'destination',
        'service_date',
        'pickup_time',
        'vehicle_type',
        'cost',
        'margin',
        'sell',
        'sar',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'cost' => 'decimal:2',
            'margin' => 'decimal:2',
            'sell' => 'decimal:2',
            'sar' => 'decimal:2',
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
