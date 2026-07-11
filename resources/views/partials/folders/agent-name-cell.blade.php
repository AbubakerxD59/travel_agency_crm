@php
    $displayName = folder_agent_display_name($folder);
    $agent = $folder->agent;
    $overviewRoute = portal_route_prefix().'.agents.overview';
    $linkToOverview = $agent
        && $agent->hasRole(\App\Models\User::ROLE_AGENT)
        && Route::has($overviewRoute);
@endphp

@if ($linkToOverview)
    <a href="{{ portal_route('agents.overview', $agent) }}"
        class="font-medium text-concierge-accent hover:underline">
        {{ $displayName }}
    </a>
@else
    <span class="font-medium text-concierge-navy">{{ $displayName }}</span>
@endif
