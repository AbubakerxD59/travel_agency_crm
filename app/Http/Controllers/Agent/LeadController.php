<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateLeadRequest;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class LeadController extends Controller
{
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
            ->paginate(15)
            ->withQueryString();

        return view('agent.leads.index', [
            'leads' => $leads,
            'search' => $search,
            'selectedSource' => $source,
            'selectedStatus' => $status,
            'sources' => Lead::query()
                ->where('agent_id', $request->user()->id)
                ->whereNotNull('source')
                ->where('source', '!=', '')
                ->distinct()
                ->orderBy('source')
                ->pluck('source')
                ->values(),
            'statuses' => Lead::statusLabels(),
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
