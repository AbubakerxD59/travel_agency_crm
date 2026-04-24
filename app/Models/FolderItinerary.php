<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderItinerary extends Model
{
    protected $fillable = [
        'folder_id',
        'sr_no',
        'airline_code',
        'airline_number',
        'class',
        'departure_date',
        'departure_airport',
        'arrival_airport',
        'departure_time',
        'arrival_time',
        'arrival_date',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'arrival_date' => 'date',
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
