<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\SyncAgentPermissionsRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Throwable;

class AgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:agents.create')->only(['index', 'store']);
        $this->middleware('can:agents.manage')->only([
            'show',
            'update',
            'destroy',
            'permissions',
            'syncPermissions',
        ]);
    }

    public function index(Request $request): View
    {
        $agents = User::role('agent')
            ->with('roles')
            ->select('users.*')
            ->orderBy('users.name')
            ->get();

        return view('admin.agents.index', [
            'agents' => $agents,
            'canManageAgents' => $request->user()->can('agents.manage'),
        ]);
    }

    public function store(StoreAgentRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $data = $request->safe()->only(['name', 'email', 'phone_number', 'password']);
                $user = User::create($data);
                $user->assignRole($request->validated('role'));

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
            $user->refresh();

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
        $this->ensureAgent($agent);

        if (! $request->expectsJson()) {
            abort(404);
        }

        return response()->json([
            'agent' => $this->agentPayload($agent),
        ]);
    }

    public function update(UpdateAgentRequest $request, User $agent): JsonResponse
    {
        $this->ensureAgent($agent);

        try {
            $data = $request->safe()->only(['name', 'email', 'phone_number']);
            if ($request->filled('password')) {
                $data['password'] = $request->validated('password');
            }
            $agent->update($data);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not update agent. Please try again.'),
            ], 500);
        }

        $agent->refresh();

        return response()->json([
            'message' => __('Agent updated successfully.'),
            'agent' => $this->agentPayload($agent),
        ]);
    }

    public function destroy(Request $request, User $agent): JsonResponse
    {
        $this->ensureAgent($agent);

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
        $this->ensureAgent($agent);

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
            'assigned' => $agent->getPermissionNames()->values()->all(),
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
            ],
        ]);
    }

    public function syncPermissions(SyncAgentPermissionsRequest $request, User $agent): JsonResponse
    {
        $this->ensureAgent($agent);

        try {
            $permissions = collect($request->input('permissions', []))
                ->reject(fn (string $name) => $name === 'agents.manage')
                ->values()
                ->all();

            $agent->syncPermissions($permissions);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not update permissions. Please try again.'),
            ], 500);
        }

        return response()->json([
            'message' => __('Permissions updated successfully.'),
            'assigned' => $agent->getPermissionNames()->values()->all(),
        ]);
    }

    private function ensureAgent(User $user): void
    {
        if (! $user->hasRole('agent')) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function agentPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'role' => $user->getRoleNames()->first(),
            'created_at' => $user->created_at?->format('M j, Y'),
        ];
    }

    private function permissionLabel(string $name): string
    {
        return (string) str($name)->replace(['.', '-'], ' ')->headline();
    }
}
