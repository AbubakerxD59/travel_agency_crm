<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'agent_cnic',
        'home_address',
        'guardian_name',
        'guardian_phone_number',
        'guardian_cnic',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Route name to use after login or when visiting "/" while authenticated.
     */
    public function defaultRedirectRoute(): string
    {
        if ($this->hasRole('super-admin')) {
            return 'admin.dashboard';
        }

        if ($this->hasRole('agent')) {
            if ($this->can('dashboard.access')) {
                return 'agent.dashboard';
            }

            if ($this->can('leads.access')) {
                return 'agent.leads.index';
            }

            if ($this->can('folders.access')) {
                return 'agent.folders.index';
            }
        }

        return 'admin.dashboard';
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'agent_id');
    }
}
