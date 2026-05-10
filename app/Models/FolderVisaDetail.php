<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderVisaDetail extends Model
{
    protected $fillable = [
        'folder_id',
        'supplier',
        'description',
        'cost',
        'margin',
        'sell',
    ];

    protected function casts(): array
    {
        return [
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
