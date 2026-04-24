<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderPackageCost extends Model
{
    protected $fillable = [
        'folder_id',
        'ticket_no',
        'ticket_date',
        'airline_from',
        'airline_to',
        'fare',
        'tax',
        'total_cost',
        'margin',
        'sell',
        'supplier',
        'pnr',
    ];

    protected function casts(): array
    {
        return [
            'ticket_date' => 'date',
            'fare' => 'decimal:2',
            'tax' => 'decimal:2',
            'total_cost' => 'decimal:2',
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
