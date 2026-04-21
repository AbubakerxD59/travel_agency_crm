<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadPackageCost extends Model
{
    protected $fillable = [
        'lead_id',
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
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
