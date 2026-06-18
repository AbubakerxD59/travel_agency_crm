<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAbbreviationRequest;
use App\Http\Requests\UpdateAbbreviationRequest;
use App\Models\Abbreviation;
use App\Support\AbbreviationResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class AbbreviationController extends Controller
{
    public function __construct(
        private readonly AbbreviationResolver $abbreviationResolver,
    ) {
        $this->middleware('can:abbreviations.create')->only(['index', 'store']);
        $this->middleware('can:abbreviations.manage')->only([
            'show',
            'update',
            'destroy',
        ]);
    }

    public function index(Request $request): View
    {
        $abbreviations = Abbreviation::query()
            ->orderBy('code')
            ->get();

        return view('admin.abbreviations.index', [
            'abbreviations' => $abbreviations,
            'canManageAbbreviations' => $request->user()->can('abbreviations.manage'),
        ]);
    }

    public function store(StoreAbbreviationRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $validated = $request->validated();

            $abbreviation = Abbreviation::query()->create([
                'code' => $validated['code'],
                'full_form' => $validated['full_form'],
            ]);
        } catch (Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('Could not create abbreviation. Please try again.'),
                ], 500);
            }

            throw $e;
        }

        $this->abbreviationResolver->flush();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Abbreviation created successfully.'),
                'abbreviation' => $this->abbreviationPayload($abbreviation),
            ]);
        }

        return redirect()
            ->route('admin.abbreviations.index')
            ->with('status', __('Abbreviation created successfully.'));
    }

    public function show(Request $request, Abbreviation $abbreviation): JsonResponse
    {
        if (! $request->expectsJson()) {
            abort(404);
        }

        return response()->json([
            'abbreviation' => $this->abbreviationPayload($abbreviation),
        ]);
    }

    public function update(UpdateAbbreviationRequest $request, Abbreviation $abbreviation): JsonResponse
    {
        try {
            $validated = $request->validated();

            $abbreviation->update([
                'code' => $validated['code'],
                'full_form' => $validated['full_form'],
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not update abbreviation. Please try again.'),
            ], 500);
        }

        $this->abbreviationResolver->flush();

        return response()->json([
            'message' => __('Abbreviation updated successfully.'),
            'abbreviation' => $this->abbreviationPayload($abbreviation->fresh()),
        ]);
    }

    public function destroy(Abbreviation $abbreviation): JsonResponse
    {
        try {
            $abbreviation->delete();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => __('Could not delete abbreviation. Please try again.'),
            ], 500);
        }

        $this->abbreviationResolver->flush();

        return response()->json([
            'message' => __('Abbreviation deleted successfully.'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function abbreviationPayload(Abbreviation $abbreviation): array
    {
        return [
            'id' => $abbreviation->id,
            'code' => $abbreviation->code,
            'full_form' => $abbreviation->full_form,
            'created_at' => $abbreviation->created_at?->format('M j, Y'),
        ];
    }
}
