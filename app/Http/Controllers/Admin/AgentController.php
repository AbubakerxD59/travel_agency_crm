<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\SyncAgentPermissionsRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:agents.create')->only(['index', 'store']);
        $this->middleware('can:agents.manage')->only([
            'show',
            'overview',
            'update',
            'destroy',
            'permissions',
            'syncPermissions',
        ]);
    }

    public function index(Request $request): View
    {
        $companyId = $request->integer('company_id') ?: null;

        $agentsQuery = User::role(User::teamRoleNames())
            ->with(['roles', 'company', 'manager'])
            ->select('users.*')
            ->latest();

        if ($companyId) {
            $agentsQuery->where('company_id', $companyId);
        }

        return view('admin.agents.index', [
            'agents' => $agentsQuery->get(),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'managers' => User::role(User::ROLE_MANAGER)->orderBy('name')->get(['id', 'name']),
            'selectedCompanyId' => $companyId,
            'canManageAgents' => $request->user()->can('agents.manage'),
        ]);
    }

    public function store(StoreAgentRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $data = $request->safe()->only([
                    'name',
                    'email',
                    'phone_number',
                    'agent_cnic',
                    'home_address',
                    'guardian_name',
                    'guardian_phone_number',
                    'guardian_cnic',
                    'company_id',
                    'manager_id',
                    'password',
                ]);
                $user = User::create($data);
                $user->assignRole($request->validated('role'));
                $user->givePermissionTo(
                    $request->validated('role') === User::ROLE_AGENT
                        ? User::defaultAgentPermissions()
                        : [
                            'dashboard.access',
                            'leads.access',
                            'folders.access',
                        ]
                );

                return $user;
            });
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Could not create agent. Please try again.'),
                ], 500);
            }

            throw $e;
        }

        if ($request->expectsJson()) {
            $user->refresh()->load(['company', 'manager']);

            return response()->json([
                'message' => __('Agent created successfully.'),
                'agent' => $this->agentPayload($user),
            ]);
        }

        return redirect()
            ->route('admin.agents.index')
            ->with('status', __('Agent created successfully.'));
    }

    public function show(Request $request, User $agent): JsonResponse
    {
        $this->ensureTeamMember($agent);

        if (! $request->expectsJson()) {
            abort(404);
        }

        return response()->json([
            'agent' => $this->agentPayload($agent),
        ]);
    }

    public function overview(User $agent): View
    {
        $this->ensureTeamMember($agent);

        $baseQuery = Lead::query()->where('agent_id', $agent->id);
        $totalLeads = (clone $baseQuery)->count();
        $totalClosed = (clone $baseQuery)->where('status', Lead::STATUS_SALE_DONE)->count();
        $totalFailed = (clone $baseQuery)->where('status', Lead::STATUS_NOT_CONVERTED)->count();
        $totalPending = max(0, $totalLeads - $totalClosed - $totalFailed);

        $dashboardAgentChart = $this->buildOverviewAgentChartData($agent, 'year');

        $leads = Lead::query()
            ->with(['company', 'destination'])
            ->where('agent_id', $agent->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.agents.overview', [
            'agent' => $agent,
            'totalLeads' => $totalLeads,
            'totalClosed' => $totalClosed,
            'totalPending' => $totalPending,
            'totalFailed' => $totalFailed,
            'dashboardAgentChart' => $dashboardAgentChart,
            'leads' => $leads,
        ]);
    }

    public function overviewPerformanceData(Request $request, User $agent): JsonResponse
    {
        $this->ensureTeamMember($agent);

        $range = (string) $request->string('range', 'year');
        $customStart = null;
        $customEnd = null;

        try {
            $customStart = $request->filled('start_date') ? Carbon::parse((string) $request->input('start_date')) : null;
            $customEnd = $request->filled('end_date') ? Carbon::parse((string) $request->input('end_date')) : null;
        } catch (\Throwable) {
            $customStart = null;
            $customEnd = null;
        }

        return response()->json(
            $this->buildOverviewAgentChartData($agent, $range, $customStart, $customEnd)
        );
    }

    public function update(UpdateAgentRequest $request, User $agent): JsonResponse
    {
        $this->ensureTeamMember($agent);

        try {
            $data = $request->safe()->only([
                'name',
                'email',
                'phone_number',
                'agent_cnic',
                'home_address',
                'guardian_name',
                'guardian_phone_number',
                'guardian_cnic',
                'company_id',
                'manager_id',
            ]);
            if ($request->filled('password')) {
                $data['password'] = $request->validated('password');
            }
            $agent->update($data);
            $agent->syncRoles([$request->validated('role')]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not update agent. Please try again.'),
            ], 500);
        }

        $agent->refresh()->load(['company', 'manager']);

        return response()->json([
            'message' => __('Agent updated successfully.'),
            'agent' => $this->agentPayload($agent),
        ]);
    }

    public function destroy(Request $request, User $agent): JsonResponse
    {
        $this->ensureTeamMember($agent);

        if ($request->user()->is($agent)) {
            return response()->json([
                'message' => __('You cannot delete your own account.'),
            ], 403);
        }

        try {
            $agent->delete();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not delete agent. Please try again.'),
            ], 500);
        }

        return response()->json([
            'message' => __('Agent deleted successfully.'),
        ]);
    }

    public function permissions(Request $request, User $agent): JsonResponse
    {
        $this->ensureTeamMember($agent);

        if (! $request->expectsJson()) {
            abort(404);
        }

        $assignable = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'agents.manage')
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $p) => [
                'name' => $p->name,
                'label' => $this->permissionLabel($p->name),
            ])
            ->values()
            ->all();

        return response()->json([
            'assignable' => $assignable,
            'assigned' => $agent->load('permissions')->permissions->pluck('name')->values()->all(),
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
            ],
        ]);
    }

    public function syncPermissions(SyncAgentPermissionsRequest $request, User $agent): JsonResponse
    {
        $this->ensureTeamMember($agent);

        try {
            $permissions = collect($request->input('permissions', []))
                ->reject(fn (string $name) => $name === 'agents.manage')
                ->values()
                ->all();

            $agent->syncPermissions($permissions);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not update permissions. Please try again.'),
            ], 500);
        }

        return response()->json([
            'message' => __('Permissions updated successfully.'),
            'assigned' => $agent->load('permissions')->permissions->pluck('name')->values()->all(),
        ]);
    }

    private function ensureTeamMember(User $user): void
    {
        if (! $user->hasAnyRole(User::teamRoleNames())) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function agentPayload(User $user): array
    {
        $user->loadMissing(['company', 'manager']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'agent_cnic' => $user->agent_cnic,
            'home_address' => $user->home_address,
            'guardian_name' => $user->guardian_name,
            'guardian_phone_number' => $user->guardian_phone_number,
            'guardian_cnic' => $user->guardian_cnic,
            'company_id' => $user->company_id,
            'manager_id' => $user->manager_id,
            'company_name' => $user->company?->name,
            'manager_name' => $user->manager?->name,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at?->format('M j, Y'),
        ];
    }

    private function permissionLabel(string $name): string
    {
        return (string) str($name)->replace(['.', '-'], ' ')->headline();
    }

    /**
     * @return array{labels: list<string>, agents: list<array{name: string, color: string, data: list<int>}>}
     */
    private function buildOverviewAgentChartData(
        User $agent,
        string $range,
        ?Carbon $customStart = null,
        ?Carbon $customEnd = null,
    ): array {
        [$start, $end, $groupByMonth] = performanceChartDateRange($range, $customStart, $customEnd);

        $bucketKeys = [];
        $labels = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ($groupByMonth) {
                $bucketKeys[] = $cursor->format('Y-m');
                $labels[] = $cursor->format('M');
                $cursor->addMonthNoOverflow();
            } else {
                $bucketKeys[] = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('d M');
                $cursor->addDay();
            }
        }

        $bucketSql = $groupByMonth
            ? "DATE_FORMAT(created_at, '%Y-%m')"
            : "DATE_FORMAT(created_at, '%Y-%m-%d')";

        $baseQuery = Lead::query()
            ->where('agent_id', $agent->id)
            ->whereBetween('created_at', [$start, $end]);

        /** @var Collection<string, int> $totalByBucket */
        $totalByBucket = (clone $baseQuery)
            ->selectRaw("{$bucketSql} as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        /** @var Collection<string, int> $closedByBucket */
        $closedByBucket = (clone $baseQuery)
            ->where('status', Lead::STATUS_SALE_DONE)
            ->selectRaw("{$bucketSql} as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        /** @var Collection<string, int> $failedByBucket */
        $failedByBucket = (clone $baseQuery)
            ->where('status', Lead::STATUS_NOT_CONVERTED)
            ->selectRaw("{$bucketSql} as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        return [
            'labels' => $labels,
            'agents' => [
                [
                    'name' => 'Total leads',
                    'color' => '#2d5a8c',
                    'data' => collect($bucketKeys)->map(fn (string $key): int => (int) ($totalByBucket[$key] ?? 0))->all(),
                ],
                [
                    'name' => 'Closed Leads',
                    'color' => '#059669',
                    'data' => collect($bucketKeys)->map(fn (string $key): int => (int) ($closedByBucket[$key] ?? 0))->all(),
                ],
                [
                    'name' => 'Failed Leads',
                    'color' => '#dc2626',
                    'data' => collect($bucketKeys)->map(fn (string $key): int => (int) ($failedByBucket[$key] ?? 0))->all(),
                ],
            ],
        ];
    }
}
