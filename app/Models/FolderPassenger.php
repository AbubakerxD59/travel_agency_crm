<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FolderPassenger extends Model
{
    /**
     * Allowed title values for passengers (delegates to {@see folder_passenger_titles()}).
     *
     * @return list<string>
     */
    public static function titles(): array
    {
        return folder_passenger_titles();
    }

    /**
     * Allowed passenger type values (delegates to {@see folder_passenger_types()}).
     *
     * @return list<string>
     */
    public static function passengerTypes(): array
    {
        return folder_passenger_types();
    }

    protected $fillable = [
        'folder_id',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'passenger_type',
        'email',
        'phone',
        'date_of_birth',
        'passport_details',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
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
