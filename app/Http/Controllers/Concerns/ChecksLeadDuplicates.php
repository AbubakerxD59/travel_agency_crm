<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\CheckLeadDuplicateRequest;
use App\Services\LeadDuplicateChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait ChecksLeadDuplicates
{
    protected function checkDuplicateLeadResponse(
        CheckLeadDuplicateRequest $request,
        string $showRouteName,
    ): JsonResponse {
        $duplicate = app(LeadDuplicateChecker::class)->find(
            $request->input('email'),
            (string) $request->input('phone_number'),
            $request->integer('exclude_lead_id') ?: null,
        );

        if ($duplicate === null) {
            return response()->json(['duplicate' => false]);
        }

        return response()->json([
            'duplicate' => true,
            'lead' => app(LeadDuplicateChecker::class)->toPayload($duplicate, $showRouteName),
        ]);
    }

    protected function redirectIfDuplicateLeadWithoutConfirmation(
        Request $request,
        ?string $email,
        string $phoneNumber,
        ?int $excludeLeadId = null,
    ): ?RedirectResponse {
        if ($request->boolean('confirm_duplicate')) {
            return null;
        }

        $duplicate = app(LeadDuplicateChecker::class)->find($email, $phoneNumber, $excludeLeadId);
        if ($duplicate === null) {
            return null;
        }

        return back()
            ->withInput()
            ->with(
                'error',
                __('A lead already exists with this email and phone number. Choose "Create new lead" to continue anyway, or cancel to discard.'),
            );
    }
}
