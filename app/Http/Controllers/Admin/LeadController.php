<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $leads = Lead::query()
            ->with(['agent', 'company', 'destination'])
            ->orderByDesc('travel_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.leads.index', [
            'leads' => $leads,
            'canCreateLeads' => $request->user()->hasRole('super-admin'),
        ]);
    }

    public function create(Request $request): View
    {
        $agents = User::role('agent')->orderBy('name')->get(['id', 'name']);
        $companies = Company::query()->with('country')->orderBy('name')->get();
        $destinations = Destination::query()->orderBy('name')->get();

        return view('admin.leads.create', [
            'agents' => $agents,
            'companies' => $companies,
            'destinations' => $destinations,
            'statuses' => Lead::statusLabels(),
        ]);
    }

    public function show(Lead $lead): View
    {
        $lead->load([
            'agent',
            'company',
            'destination',
            'itineraries',
            'passengers',
            'packageCosts',
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

        return view('admin.leads.edit', [
            'lead' => $lead,
            'agents' => $agents,
            'companies' => $companies,
            'destinations' => $destinations,
            'statuses' => Lead::statusLabels(),
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
