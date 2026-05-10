<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<FolderPayment, $this>
     */
    public function folderPayments(): HasMany
    {
        return $this->hasMany(FolderPayment::class);
    }
}
