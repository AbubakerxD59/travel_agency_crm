<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ChecksLeadDuplicates;
use App\Http\Controllers\Concerns\ResolvesClosedLeadsChart;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignLeadRequest;
use App\Http\Requests\CheckLeadDuplicateRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Lead;
use App\Models\User;
use App\Notifications\LeadAssignedNotification;
use App\Services\AgentWebPushService;
use App\Services\LeadCsvExporter;
use App\Support\LeadListingQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class LeadController extends Controller
{
    use ChecksLeadDuplicates;
    use ResolvesClosedLeadsChart;

    public function __construct()
    {
        $this->middleware('can:leads.access')->only(['index', 'show', 'export']);
        $this->middleware('role:super-admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $filters = LeadListingQuery::adminFilters($request);
        $leadsQuery = $filters['query'];

        $leads = (clone $leadsQuery)
            ->paginate(30)
            ->withQueryString();

        $statsQuery = LeadListingQuery::adminStatsQuery(
            $filters['search'],
            $filters['agent_id'],
            $filters['company_id'],
            $filters['source'],
            $filters['status'],
            $filters['start_bound'],
            $filters['end_bound'],
        );

        $totalLeads = (clone $statsQuery)->count();
        $totalClosed = (clone $statsQuery)->where('status', Lead::STATUS_SALE_DONE)->count();
        $totalFailed = (clone $statsQuery)->where('status', Lead::STATUS_NOT_CONVERTED)->count();
        $totalPending = max(0, $totalLeads - $totalClosed - $totalFailed);

        $leadsSuccessRatePercent = $totalLeads > 0
            ? min(100, (int) round(($totalClosed / $totalLeads) * 100))
            : 0;

        [$chartStart, $chartEnd, $chartGroupByMonth] = performanceChartDateRange(
            $filters['date_range'],
            $filters['start_bound'],
            $filters['end_bound'],
        );

        $closedLeadsChart = buildClosedLeadsChartData(
            $chartStart,
            $chartEnd,
            $chartGroupByMonth,
            $filters['source'] !== '' ? $filters['source'] : null,
            $filters['agent_id'],
            $filters['company_id'],
        );

        return view('admin.leads.index', [
            'leads' => $leads,
            'search' => $filters['search'],
            'selectedAgentId' => $filters['agent_id'],
            'selectedCompanyId' => $filters['company_id'],
            'selectedSource' => $filters['source'],
            'selectedStatus' => $filters['status'],
            'selectedDateRange' => $filters['date_range'],
            'selectedStartDate' => $filters['start_date'],
            'selectedEndDate' => $filters['end_date'],
            'selectedDateFilterLabel' => $filters['date_label'],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => Lead::statusLabels(),
            'agents' => User::role(User::ROLE_AGENT)->orderBy('name')->get(['id', 'name', 'company_id']),
            'canCreateLeads' => $request->user()->hasRole('super-admin'),
            'totalLeads' => $totalLeads,
            'totalClosed' => $totalClosed,
            'totalPending' => $totalPending,
            'totalFailed' => $totalFailed,
            'leadsSuccessRatePercent' => $leadsSuccessRatePercent,
            'closedLeadsChart' => $closedLeadsChart,
            'chartDateLabel' => $filters['date_label'],
            'chartDateRange' => $filters['date_range'],
            'chartStartDate' => $filters['start_date'],
            'chartEndDate' => $filters['end_date'],
            'chartSource' => $filters['source'],
        ]);
    }

    public function export(Request $request, LeadCsvExporter $exporter): StreamedResponse
    {
        $filters = LeadListingQuery::adminFilters($request);
        $leads = (clone $filters['query'])->get();

        return $exporter->download($leads, LeadCsvExporter::CONTEXT_ADMIN);
    }

    public function closedLeadsChart(Request $request): JsonResponse
    {
        return $this->closedLeadsChartResponse($request);
    }

    public function checkDuplicate(CheckLeadDuplicateRequest $request): JsonResponse
    {
        return $this->checkDuplicateLeadResponse($request, 'admin.leads.show');
    }

    public function assign(AssignLeadRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($redirect = $this->redirectIfDuplicateLeadWithoutConfirmation(
            $request,
            $data['email'] ?? null,
            $data['phone_number'],
        )) {
            return $redirect;
        }

        $destinationId = Destination::query()->value('id');
        if ($destinationId === null) {
            return back()->withInput()->with('error', __('Please add a destination first, then assign the lead.'));
        }

        $agentId = (int) $data['agent_id'];
        $agentName = User::withTrashed()->whereKey($agentId)->value('name');

        $lead = Lead::create([
            'agent_id' => $agentId,
            'agent_name' => $agentName,
            'lead_assign_date' => now(),
            'customer_name' => $data['customer_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'company_id' => $data['company_id'],
            'city' => $data['city'],
            'total_passengers' => $data['total_passengers'] ?? null,
            'source' => $data['source'],
            'notes' => $data['notes'] ?? null,
            'order_type' => 'Assigned',
            'status' => Lead::STATUS_NEW,
            'destination_id' => $destinationId,
            'travel_date' => now()->toDateString(),
            'vendor_reference' => null,
            'balance_due_date' => null,
            'flight_itinerary' => null,
            'ziarat_makkah' => false,
            'ziarat_madinah' => false,
        ]);

        $this->notifyAssignedAgent($lead, null, $agentId);

        return redirect()
            ->route('admin.leads.index')
            ->with('status', __('Lead assigned successfully.'));
    }

    public function updateAssign(AssignLeadRequest $request, Lead $lead): RedirectResponse
    {
        $data = $request->validated();
        $previousAgentId = (int) ($lead->agent_id ?? 0);
        $nextAgentId = (int) $data['agent_id'];

        $nextAgentName = User::withTrashed()->whereKey($nextAgentId)->value('name');

        $lead->update([
            'agent_id' => $nextAgentId,
            'agent_name' => $nextAgentName,
            'lead_assign_date' => (int) $lead->agent_id !== $nextAgentId ? now() : $lead->lead_assign_date,
            'customer_name' => $data['customer_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'company_id' => $data['company_id'],
            'city' => $data['city'],
            'total_passengers' => $data['total_passengers'] ?? null,
            'source' => $data['source'],
            'notes' => $data['notes'] ?? null,
        ]);
        $lead->refresh();
        $this->notifyAssignedAgent($lead, $previousAgentId, $nextAgentId);

        return redirect()
            ->route('admin.leads.index')
            ->with('status', __('Lead updated successfully.'));
    }

    public function create(Request $request): View
    {
        $agents = User::role(User::ROLE_AGENT)->orderBy('name')->get(['id', 'name', 'company_id']);
        $companies = Company::query()->with('country')->orderBy('name')->get();
        $destinations = Destination::query()->orderBy('name')->get();

        return view('agent.leads.create', [
            'agents' => $agents,
            'companies' => $companies,
            'destinations' => $destinations,
            'statuses' => Lead::statusLabels(),
            'leadRoutePrefix' => 'admin',
            'leadLayout' => 'layouts.admin',
        ]);
    }

    public function show(Lead $lead): View
    {
        $lead->load([
            'agent',
            'company',
        ]);

        return view('admin.leads.show', [
            'lead' => $lead,
        ]);
    }

    public function edit(Lead $lead): View
    {
        $lead->load(['itineraries', 'passengers', 'packageCosts']);
        $agents = User::role(User::ROLE_AGENT)->orderBy('name')->get(['id', 'name', 'company_id']);
        $companies = Company::query()->with('country')->orderBy('name')->get();
        $destinations = Destination::query()->orderBy('name')->get();

        return view('agent.leads.edit', [
            'lead' => $lead,
            'agents' => $agents,
            'companies' => $companies,
            'destinations' => $destinations,
            'statuses' => Lead::statusLabels(),
            'leadRoutePrefix' => 'admin',
            'leadLayout' => 'layouts.admin',
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request): void {
                $payload = $request->safe()->only([
                    'agent_id',
                    'order_type',
                    'vendor_reference',
                    'company_id',
                    'status',
                    'destination_id',
                    'travel_date',
                    'balance_due_date',
                    'flight_itinerary',
                    'ziarat_makkah',
                    'ziarat_madinah',
                ]);
                $payload['lead_assign_date'] = ! empty($payload['agent_id']) ? now() : null;
                $payload['agent_name'] = ! empty($payload['agent_id'])
                    ? User::withTrashed()->whereKey((int) $payload['agent_id'])->value('name')
                    : null;

                $lead = Lead::create($payload);

                $itineraries = $request->safe()->input('itineraries', []);
                $lead->itineraries()->createMany($itineraries);

                $passengers = $request->safe()->input('passengers', []);
                $lead->passengers()->createMany($passengers);

                $packageCosts = $request->safe()->input('package_costs', []);
                $lead->packageCosts()->createMany($packageCosts);

                $this->notifyAssignedAgent($lead, null, (int) ($payload['agent_id'] ?? 0));
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', __('Could not create lead. Please try again.'));
        }

        return redirect()
            ->route('admin.leads.index')
            ->with('status', __('Lead created successfully.'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $lead): void {
                $previousAgentId = (int) ($lead->agent_id ?? 0);
                $payload = $request->safe()->only([
                    'agent_id',
                    'order_type',
                    'vendor_reference',
                    'company_id',
                    'status',
                    'destination_id',
                    'travel_date',
                    'balance_due_date',
                    'flight_itinerary',
                    'ziarat_makkah',
                    'ziarat_madinah',
                ]);

                $nextAgentId = $payload['agent_id'] ?? null;
                $payload['lead_assign_date'] = $nextAgentId === null
                    ? null
                    : ((int) $lead->agent_id !== (int) $nextAgentId ? now() : $lead->lead_assign_date);
                $payload['agent_name'] = $nextAgentId === null
                    ? null
                    : User::withTrashed()->whereKey((int) $nextAgentId)->value('name');

                $lead->update($payload);

                $lead->itineraries()->delete();
                $lead->passengers()->delete();
                $lead->packageCosts()->delete();

                $lead->itineraries()->createMany($request->safe()->input('itineraries', []));
                $lead->passengers()->createMany($request->safe()->input('passengers', []));
                $lead->packageCosts()->createMany($request->safe()->input('package_costs', []));

                $this->notifyAssignedAgent($lead->fresh(), $previousAgentId, (int) ($nextAgentId ?? 0));
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', __('Could not update lead. Please try again.'));
        }

        return redirect()
            ->route('admin.leads.index')
            ->with('status', __('Lead updated successfully.'));
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        try {
            $lead->delete();
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', __('Could not delete lead. Please try again.'));
        }

        return redirect()
            ->route('admin.leads.index')
            ->with('status', __('Lead deleted successfully.'));
    }

    private function notifyAssignedAgent(Lead $lead, ?int $previousAgentId, int $nextAgentId): void
    {
        if ($nextAgentId <= 0) {
            return;
        }

        // Only send notification when lead is newly assigned or reassigned.
        if ($previousAgentId !== null && $previousAgentId === $nextAgentId) {
            return;
        }

        $agent = User::query()->find($nextAgentId);
        if (! $agent || ! $agent->hasRole('agent')) {
            return;
        }

        $isReassigned = $previousAgentId !== null && $previousAgentId > 0 && $previousAgentId !== $nextAgentId;

        $agent->notify(new LeadAssignedNotification($lead, $isReassigned));

        app(AgentWebPushService::class)->sendLeadAssigned($agent, $lead, $isReassigned);

        // Keep sent_at null until polling endpoint fetches the notification once.
        if (Schema::hasColumn('notifications', 'sent_at')) {
            $agent->notifications()
                ->where('type', LeadAssignedNotification::class)
                ->where('data->lead_id', $lead->id)
                ->latest()
                ->limit(1)
                ->update(['sent_at' => null]);
        }
    }
}
