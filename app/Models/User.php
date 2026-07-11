<?php

namespace App\Models;

use App\Support\AgentCnicPhotoStorage;
use Illuminate\Database\Eloquent\Builder;
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
     * Limit users to the viewer's company when the viewer is a manager.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeVisibleToStaff(Builder $query, ?self $viewer): Builder
    {
        if (! $viewer?->hasRole(self::ROLE_MANAGER)) {
            return $query;
        }

        if ($viewer->company_id === null) {
            return $query->whereRaw('0 = 1');
        }

        return $query->where('users.company_id', $viewer->company_id);
    }

    /**
     * Agents the staff viewer is allowed to see (managers: same-company agents only).
     *
     * @return Builder<User>
     */
    public static function agentsVisibleTo(?self $viewer): Builder
    {
        return static::role(self::ROLE_AGENT)->visibleToStaff($viewer);
    }

    /**
     * Users who may own leads/folders for this viewer.
     * Super admin: all agents and managers. Manager: company agents + themself.
     *
     * @return Builder<User>
     */
    public static function recordAssigneesVisibleTo(?self $viewer): Builder
    {
        if ($viewer?->hasRole(self::ROLE_MANAGER)) {
            return static::query()
                ->where(function (Builder $builder) use ($viewer): void {
                    $builder
                        ->whereIn(
                            'users.id',
                            static::agentsVisibleTo($viewer)->select('users.id'),
                        )
                        ->orWhere('users.id', $viewer->id);
                });
        }

        return static::role(self::teamRoleNames());
    }

    public function isVisibleToStaff(?self $viewer): bool
    {
        if (! $viewer?->hasRole(self::ROLE_MANAGER)) {
            return true;
        }

        if (! $this->hasRole(self::ROLE_AGENT)) {
            return false;
        }

        if ($viewer->company_id === null || $this->company_id === null) {
            return false;
        }

        return (int) $this->company_id === (int) $viewer->company_id;
    }

    /**
     * @return list<string>
     */
    public static function assignableAgentPermissionNames(): array
    {
        return [
            'dashboard.access',
            'leads.access',
            'leads.create',
            'leads.export',
            'folders.access',
            'folders.edit',
            'folders.edit_locked',
        ];
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
            'folders.edit',
        ];
    }

    /**
     * Managers receive the full permission set (same as super admin).
     *
     * @return list<string>
     */
    public static function defaultManagerPermissions(): array
    {
        return [
            'dashboard.access',
            'agents.create',
            'agents.manage',
            'leads.access',
            'leads.create',
            'leads.export',
            'folders.access',
            'folders.edit',
            'folders.edit_locked',
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
            if ($this->can('dashboard.access')) {
                return 'manager.dashboard';
            }

            if ($this->can('leads.access')) {
                return 'manager.leads.index';
            }

            if ($this->can('folders.access')) {
                return 'manager.folders.index';
            }

            return 'manager.dashboard';
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
