<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:companies.create')->only(['index', 'store']);
        $this->middleware('can:companies.manage')->only([
            'show',
            'update',
            'destroy',
        ]);
    }

    public function index(Request $request): View
    {
        $companies = Company::query()
            ->with('country')
            ->orderBy('name')
            ->get();

        $countries = Country::query()->orderBy('name')->get();

        return view('admin.companies.index', [
            'companies' => $companies,
            'countries' => $countries,
            'canManageCompanies' => $request->user()->can('companies.manage'),
        ]);
    }

    public function store(StoreCompanyRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $company = Company::create($request->safe()->only(['name', 'country_id']));
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Could not create company. Please try again.'),
                ], 500);
            }

            throw $e;
        }

        $company->load('country');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Company created successfully.'),
                'company' => $this->companyPayload($company),
            ]);
        }

        return redirect()
            ->route('admin.companies.index')
            ->with('status', __('Company created successfully.'));
    }

    public function show(Request $request, Company $company): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(404);
        }

        $company->load('country');

        return response()->json([
            'company' => $this->companyPayload($company),
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        try {
            $company->update($request->safe()->only(['name', 'country_id']));
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not update company. Please try again.'),
            ], 500);
        }

        $company->refresh();
        $company->load('country');

        return response()->json([
            'message' => __('Company updated successfully.'),
            'company' => $this->companyPayload($company),
        ]);
    }

    public function destroy(Request $request, Company $company): JsonResponse
    {
        try {
            $company->delete();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not delete company. Please try again.'),
            ], 500);
        }

        return response()->json([
            'message' => __('Company deleted successfully.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function companyPayload(Company $company): array
    {
        return [
            'id' => $company->id,
            'name' => $company->name,
            'country_id' => $company->country_id,
            'country_name' => $company->country?->name,
            'created_at' => $company->created_at?->format('M j, Y'),
        ];
    }
}
