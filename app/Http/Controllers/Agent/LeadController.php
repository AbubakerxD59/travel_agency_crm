<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Concerns\ResolvesClosedLeadsChart;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAgentLeadRequest;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeadController extends Controller
{
    use ResolvesClosedLeadsChart;

    public function __construct()
    {
        $this->middleware('can:leads.access')->only(['index', 'show', 'updateStatus', 'closedLeadsChart']);
        $this->middleware('can:leads.create')->only(['store']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $source = trim((string) $request->query('source', ''));
        $status = trim((string) $request->query('status', ''));

        $leadsQuery = Lead::query()
            ->with(['agent', 'company', 'destination'])
            ->where('agent_id', $request->user()->id)
            ->latest();

        if ($source !== '') {
            $leadsQuery->where('source', $source);
        }

        if ($status !== '') {
            $leadsQuery->where('status', $status);
        }

        if ($search !== '') {
            $leadsQuery->where(function ($query) use ($search): void {
                $query
                    ->where('customer_name', 'like', '%'.$search.'%')
                    ->orWhere('phone_number', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $leads = $leadsQuery
            ->paginate(30)
            ->withQueryString();

        $chartDateFilter = resolveLeadDateRangeFilter('year', '', '', 'year');
        [$chartStart, $chartEnd, $chartGroupByMonth] = performanceChartDateRange(
            $chartDateFilter['range'],
            $chartDateFilter['start'],
            $chartDateFilter['end'],
        );

        $agentSourceOptions = getAgentLeadSources();
        $chartSource = $source !== '' && array_key_exists($source, $agentSourceOptions) ? $source : '';

        $closedLeadsChart = buildClosedLeadsChartData(
            $chartStart,
            $chartEnd,
            $chartGroupByMonth,
            $chartSource !== '' ? $chartSource : null,
            (int) $request->user()->id,
            null,
            $agentSourceOptions,
        );

        return view('agent.leads.index', [
            'leads' => $leads,
            'search' => $search,
            'selectedSource' => $source,
            'selectedStatus' => $status,
            'statuses' => Lead::statusLabels(),
            'canCreateLeads' => $request->user()->can('leads.create'),
            'companies' => $request->user()->can('leads.create')
                ? Company::query()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'closedLeadsChart' => $closedLeadsChart,
            'chartDateLabel' => $chartDateFilter['label'],
            'chartDateRange' => $chartDateFilter['range'],
            'chartStartDate' => $chartDateFilter['startDate'],
            'chartEndDate' => $chartDateFilter['endDate'],
            'chartSource' => $chartSource,
            'agentSourceOptions' => $agentSourceOptions,
        ]);
    }

    public function closedLeadsChart(Request $request): JsonResponse
    {
        return $this->closedLeadsChartResponse($request, (int) $request->user()->id);
    }

    public function store(StoreAgentLeadRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $companyId = $data['company_id'] ?? Company::query()->value('id');
        if ($companyId === null) {
            return back()->withInput()->with('error', __('Please add a company first, then create the lead.'));
        }

        $destinationId = Destination::query()->value('id');
        if ($destinationId === null) {
            return back()->withInput()->with('error', __('Please add a destination first, then create the lead.'));
        }

        $agentId = (int) $request->user()->id;
        $agentName = \App\Models\User::withTrashed()->whereKey($agentId)->value('name');

        Lead::create([
            'agent_id' => $agentId,
            'agent_name' => $agentName,
            'lead_assign_date' => now(),
            'customer_name' => $data['customer_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'company_id' => $companyId,
            'city' => $data['city'] ?? null,
            'total_passengers' => $data['total_passengers'] ?? null,
            'source' => $data['source'] ?? null,
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

        return redirect()
            ->route('agent.leads.index')
            ->with('status', __('Lead created successfully.'));
    }

    public function show(Lead $lead): View
    {
        if ((int) $lead->agent_id !== (int) request()->user()->id) {
            abort(404);
        }

        $lead->load([
            'agent',
            'company',
        ]);

        return view('agent.leads.show', [
            'lead' => $lead,
            'statuses' => Lead::statusLabels(),
        ]);
    }

    public function updateStatus(Request $request, Lead $lead): JsonResponse
    {
        if ((int) $lead->agent_id !== (int) $request->user()->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(Lead::statusKeys())],
            'not_converted_reason' => ['nullable', 'string', 'max:1000', 'required_if:status,'.Lead::STATUS_NOT_CONVERTED],
        ]);
        $reason = isset($validated['not_converted_reason']) ? trim((string) $validated['not_converted_reason']) : null;
        if ($validated['status'] === Lead::STATUS_NOT_CONVERTED && $reason === '') {
            throw ValidationException::withMessages([
                'not_converted_reason' => __('Please provide a reason for not converted.'),
            ]);
        }

        $lead->update([
            'status' => $validated['status'],
            'not_converted_reason' => $validated['status'] === Lead::STATUS_NOT_CONVERTED
                ? $reason
                : null,
        ]);

        return response()->json([
            'message' => __('Lead status updated successfully.'),
            'status' => $lead->status,
            'status_label' => $lead->statusLabel(),
            'status_pill_class' => $lead->statusPillClass(),
            'not_converted_reason' => $lead->not_converted_reason,
        ]);
    }
}
