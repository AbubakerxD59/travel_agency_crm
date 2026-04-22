<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $leads = Lead::query()
            ->with(['agent', 'company', 'destination'])
            ->where('agent_id', $request->user()->id)
            ->orderByDesc('travel_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('agent.leads.index', [
            'leads' => $leads,
            'canCreateLeads' => true,
        ]);
    }

    public function show(Lead $lead): View
    {
        if ((int) $lead->agent_id !== (int) request()->user()->id) {
            abort(404);
        }

        $lead->load([
            'agent',
            'company',
            'destination',
            'itineraries',
            'passengers',
            'packageCosts',
        ]);

        return view('agent.leads.show', [
            'lead' => $lead,
        ]);
    }

    public function edit(Lead $lead): View
    {
        if ((int) $lead->agent_id !== (int) request()->user()->id) {
            abort(404);
        }

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
            'leadRoutePrefix' => 'agent',
            'leadLayout' => 'layouts.agent',
        ]);
    }

    public function update(UpdateLeadRequest $request, Lead $lead): RedirectResponse
    {
        if ((int) $lead->agent_id !== (int) $request->user()->id) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $lead): void {
                $lead->update($request->safe()->only([
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
            ->route('agent.leads.index')
            ->with('status', __('Lead updated successfully.'));
    }
}
