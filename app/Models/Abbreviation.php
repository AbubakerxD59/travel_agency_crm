<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abbreviation extends Model
{
    protected $fillable = [
        'code',
        'full_form',
    ];

    protected static function booted(): void
    {
        static::saving(function (Abbreviation $abbreviation): void {
            $abbreviation->code = strtoupper(trim($abbreviation->code));
            $abbreviation->full_form = trim($abbreviation->full_form);
        });
    }
}
