<?php

namespace App\Models;

use App\Support\AgentCnicPhotoStorage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    public const ROLE_AGENT = 'agent';

    public const ROLE_MANAGER = 'manager';

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * @return list<string>
     */
    public static function teamRoleNames(): array
    {
        return [self::ROLE_AGENT, self::ROLE_MANAGER];
    }

    /**
     * @return list<string>
     */
    public static function defaultAgentPermissions(): array
    {
        return [
            'dashboard.access',
            'leads.access',
            'leads.create',
            'folders.access',
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'direct_line',
        'agent_cnic',
        'agent_cnic_photo',
        'home_address',
        'guardian_name',
        'guardian_phone_number',
        'guardian_cnic',
        'company_id',
        'manager_id',
        'password',
    ];

    protected $appends = [
        'role_name',
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
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user): void {
            if ($user->isForceDeleting()) {
                return;
            }

            $name = trim((string) $user->name);
            if ($name === '') {
                return;
            }

            Folder::query()->where('agent_id', $user->id)->update(['agent_name' => $name]);
            Lead::query()->where('agent_id', $user->id)->update(['agent_name' => $name]);
        });
    }

    /**
     * Route name to use after login or when visiting "/" while authenticated.
     */
    public function defaultRedirectRoute(): string
    {
        if ($this->hasRole(self::ROLE_MANAGER)) {
            return 'admin.dashboard';
        }

        if ($this->hasRole(self::ROLE_AGENT)) {
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
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id')->withTrashed();
    }

    /**
     * @return HasMany<User, $this>
     */
    public function managedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    /**
     * @return HasMany<Lead, $this>
     */
    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'agent_id');
    }

    /**
     * @return HasMany<PushSubscription, $this>
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * @return HasMany<Folder, $this>
     */
    public function roleName(): string
    {
        return $this->roles->first()?->name ?? 'agent';
    }

    public function agentCnicPhotoUrl(): ?string
    {
        return app(AgentCnicPhotoStorage::class)->url($this->agent_cnic_photo);
    }
}
