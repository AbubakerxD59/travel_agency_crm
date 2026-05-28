@php
    $displayName = folder_agent_display_name($folder);
    $agent = $folder->agent;
    $linkToOverview = $agent
        && $agent->hasRole(\App\Models\User::ROLE_AGENT)
        && Route::has('admin.agents.overview');
@endphp

@if ($linkToOverview)
    <a href="{{ route('admin.agents.overview', $agent) }}"
        class="font-medium text-concierge-accent hover:underline">
        {{ $displayName }}
    </a>
@else
    <span class="font-medium text-concierge-navy">{{ $displayName }}</span>
@endif
