<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FolderController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search')->value());
        $agentId = $request->integer('agent_id') ?: null;
        $companyId = $request->integer('company_id') ?: null;
        $destinationId = $request->integer('destination_id') ?: null;

        $folders = Folder::query()
            ->with(['agent', 'company', 'destination'])
            ->when($agentId !== null, fn ($query) => $query->where('agent_id', $agentId))
            ->when($companyId !== null, fn ($query) => $query->where('company_id', $companyId))
            ->when($destinationId !== null, fn ($query) => $query->where('destination_id', $destinationId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('order_type', 'like', '%'.$search.'%')
                        ->orWhere('vendor_reference', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.folders.index', [
            'folders' => $folders,
            'search' => $search,
            'selectedAgentId' => $agentId,
            'selectedCompanyId' => $companyId,
            'selectedDestinationId' => $destinationId,
            'agents' => User::role('agent')->orderBy('name')->get(['id', 'name']),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Folder $folder): View
    {
        $folder->load(['agent', 'company', 'destination', 'itineraries', 'passengers', 'packageCosts', 'hotelDetails']);

        return view('admin.folders.show', [
            'folder' => $folder,
        ]);
    }
}
