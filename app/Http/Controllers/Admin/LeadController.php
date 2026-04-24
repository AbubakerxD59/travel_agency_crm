<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignLeadRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class LeadController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:leads.access')->only(['index', 'show']);
        $this->middleware('role:super-admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $agentId = $request->integer('agent_id') ?: null;
        $companyId = $request->integer('company_id') ?: null;
        $source = trim((string) $request->query('source', ''));
        $status = trim((string) $request->query('status', ''));

        $leadsQuery = Lead::query()
            ->with(['agent', 'company', 'destination'])
            ->latest();

        if ($companyId !== null) {
            $leadsQuery->where('company_id', $companyId);
        }

        if ($agentId !== null) {
            $leadsQuery->where('agent_id', $agentId);
        }

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
            ->paginate(15)
            ->withQueryString();

        $totalLeads = Lead::query()->count();
        $totalClosed = Lead::query()->where('status', Lead::STATUS_SALE_DONE)->count();
        $totalFailed = Lead::query()->where('status', Lead::STATUS_NOT_CONVERTED)->count();
        $totalPending = max(0, $totalLeads - $totalClosed - $totalFailed);

        $leadsSuccessRatePercent = $totalLeads > 0
            ? min(100, (int) round(($totalClosed / $totalLeads) * 100))
            : 0;

        return view('admin.leads.index', [
            'leads' => $leads,
            'search' => $search,
            'selectedAgentId' => $agentId,
            'selectedCompanyId' => $companyId,
            'selectedSource' => $source,
            'selectedStatus' => $status,
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'sources' => Lead::query()
                ->whereNotNull('source')
                ->where('source', '!=', '')
                ->distinct()
                ->orderBy('source')
                ->pluck('source')
                ->values(),
            'statuses' => Lead::statusLabels(),
            'agents' => User::role('agent')->orderBy('name')->get(['id', 'name']),
            'canCreateLeads' => $request->user()->hasRole('super-admin'),
            'totalLeads' => $totalLeads,
            'totalClosed' => $totalClosed,
            'totalPending' => $totalPending,
            'totalFailed' => $totalFailed,
            'leadsSuccessRatePercent' => $leadsSuccessRatePercent,
        ]);
    }

    public function assign(AssignLeadRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $companyId = $data['company_id'] ?? Company::query()->value('id');
        if ($companyId === null) {
            return back()->withInput()->with('error', __('Please add a company first, then assign the lead.'));
        }

        $destinationId = Destination::query()->value('id');
        if ($destinationId === null) {
            return back()->withInput()->with('error', __('Please add a destination first, then assign the lead.'));
        }

        Lead::create([
            'agent_id' => $data['agent_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'company_id' => $companyId,
            'city' => $data['city'] ?? null,
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
            ->route('admin.leads.index')
            ->with('status', __('Lead assigned successfully.'));
    }

    public function updateAssign(AssignLeadRequest $request, Lead $lead): RedirectResponse
    {
        $data = $request->validated();

        $companyId = $data['company_id'] ?? $lead->company_id ?? Company::query()->value('id');
        if ($companyId === null) {
            return back()->withInput()->with('error', __('Please add a company first, then update the lead.'));
        }

        $lead->update([
            'agent_id' => $data['agent_id'] ?? null,
            'customer_name' => $data['customer_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'company_id' => $companyId,
            'city' => $data['city'] ?? null,
            'source' => $data['source'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.leads.index')
            ->with('status', __('Lead updated successfully.'));
    }

    public function create(Request $request): View
    {
        $agents = User::role('agent')->orderBy('name')->get(['id', 'name']);
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
        $agents = User::role('agent')->orderBy('name')->get(['id', 'name']);
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
                $lead = Lead::create($request->safe()->only([
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
                ]));

                $itineraries = $request->safe()->input('itineraries', []);
                $lead->itineraries()->createMany($itineraries);

                $passengers = $request->safe()->input('passengers', []);
                $lead->passengers()->createMany($passengers);

                $packageCosts = $request->safe()->input('package_costs', []);
                $lead->packageCosts()->createMany($packageCosts);
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
                $lead->update($request->safe()->only([
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
                ]));

                $lead->itineraries()->delete();
                $lead->passengers()->delete();
                $lead->packageCosts()->delete();

                $lead->itineraries()->createMany($request->safe()->input('itineraries', []));
                $lead->passengers()->createMany($request->safe()->input('passengers', []));
                $lead->packageCosts()->createMany($request->safe()->input('package_costs', []));
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
}
