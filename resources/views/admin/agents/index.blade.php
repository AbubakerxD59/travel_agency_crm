@extends('layouts.admin')

@section('title', 'Agents')

@section('content')
    <div id="js-agents-config" class="hidden" data-url-base="{{ route('admin.agents.index') }}"
        data-can-manage="{{ $canManageAgents ? '1' : '0' }}" data-current-user-id="{{ auth()->id() }}"
        data-actions-colspan="{{ $canManageAgents ? 6 : 5 }}"></div>

    <div class="mx-auto max-w-7xl">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Agents</h1>
            </div>
            <button type="button" id="open-agent-modal"
                class="inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-xl bg-concierge-navy px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 transition hover:bg-concierge-navy-deep">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add new
            </button>
        </div>

        <div class="mt-6">
            <label for="agent-list-filter" class="block text-sm font-medium text-concierge-navy">Search agents</label>
            <input id="agent-list-filter" type="search" placeholder="Filter by name, email, or phone…" autocomplete="off"
                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
        </div>

        <div
            class="agents-list-wrap mt-4 overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-6">
            <div class="overflow-x-auto">
                <table id="agents-index-table" class="min-w-full text-left text-sm">
                    <thead>
                        <tr
                            class="border-b border-slate-100 bg-slate-50/80 text-xs font-semibold uppercase tracking-wide text-concierge-muted">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Phone</th>
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Added</th>
                            @if ($canManageAgents)
                                <th class="px-6 py-4 text-right">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($agents as $agent)
                            <tr class="hover:bg-slate-50/50" data-agent-id="{{ $agent->id }}"
                                data-search-text="{{ e(mb_strtolower($agent->name . ' ' . $agent->email . ' ' . ($agent->phone_number ?? ''), 'UTF-8')) }}">
                                <td class="px-6 py-4 font-medium text-concierge-navy">
                                    <a href="{{ route('admin.agents.overview', $agent) }}" class="hover:underline">
                                        {{ $agent->name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-concierge-muted">
                                    <a href="{{ route('admin.agents.overview', $agent) }}" class="hover:underline">
                                        {{ $agent->email }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-concierge-muted">
                                    <a href="{{ route('admin.agents.overview', $agent) }}" class="hover:underline">
                                        {{ $agent->phone_number ?? '—' }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="concierge-pill concierge-pill-contacted">{{ $agent->roles->first()?->name ?? 'agent' }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-concierge-muted">
                                    {{ $agent->created_at?->format('M j, Y') }}</td>
                                @if ($canManageAgents)
                                    <td class="px-6 py-4 text-right">
                                        <div class="inline-flex flex-wrap items-center justify-end gap-1">
                                            <button type="button"
                                                class="agent-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy"
                                                data-edit-agent="{{ $agent->id }}" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="agent-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-accent"
                                                data-permissions-agent="{{ $agent->id }}" title="Permissions">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H3.75v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="agent-row-action cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-40"
                                                data-delete-agent="{{ $agent->id }}"
                                                title="{{ auth()->id() === $agent->id ? 'You cannot delete your own account' : 'Delete' }}"
                                                @if (auth()->id() === $agent->id) disabled @endif>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                                    aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr class="agents-index-empty">
                                <td colspan="{{ $canManageAgents ? 6 : 5 }}"
                                    class="px-6 py-10 text-center text-sm text-concierge-muted">
                                    No agents yet. Use “Add new” to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p id="agents-filter-no-results" class="hidden px-6 py-8 text-center text-sm text-concierge-muted"
                role="status">
                No agents match your search.
            </p>
        </div>
    </div>

    {{-- Modal --}}
    <div id="agent-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
        aria-hidden="true" role="dialog" aria-labelledby="agent-modal-title">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 id="agent-modal-title" class="text-lg font-semibold text-concierge-navy">Add new agent</h2>
                <button type="button" data-close-agent-modal
                    class="rounded-lg p-2 text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy"
                    aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="store-agent-form" method="POST" action="{{ route('admin.agents.store') }}"
                class="space-y-4 px-6 py-5">
                @csrf

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="modal_name" class="block text-sm font-medium text-concierge-navy">Name</label>
                        <input id="modal_name" name="name" type="text" required autocomplete="name"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>

                    <div>
                        <label for="modal_email" class="block text-sm font-medium text-concierge-navy">Email</label>
                        <input id="modal_email" name="email" type="email" required autocomplete="email"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>

                    <div>
                        <label for="modal_phone_number" class="block text-sm font-medium text-concierge-navy">Phone
                            number</label>
                        <input id="modal_phone_number" name="phone_number" type="text" required autocomplete="tel"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>

                    <div>
                        <label for="modal_agent_cnic" class="block text-sm font-medium text-concierge-navy">Agent
                            CNIC</label>
                        <input id="modal_agent_cnic" name="agent_cnic" type="text"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>

                    <div class="col-span-full">
                        <label for="modal_home_address" class="block text-sm font-medium text-concierge-navy">Home
                            Address</label>
                        <textarea id="modal_home_address" name="home_address" rows="3"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20"></textarea>
                    </div>

                    <div>
                        <label for="modal_guardian_name" class="block text-sm font-medium text-concierge-navy">Guardian
                            Name</label>
                        <input id="modal_guardian_name" name="guardian_name" type="text"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>

                    <div>
                        <label for="modal_guardian_phone_number"
                            class="block text-sm font-medium text-concierge-navy">Guardian Phone Number</label>
                        <input id="modal_guardian_phone_number" name="guardian_phone_number" type="text"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>

                    <div>
                        <label for="modal_guardian_cnic" class="block text-sm font-medium text-concierge-navy">Guardian
                            CNIC</label>
                        <input id="modal_guardian_cnic" name="guardian_cnic" type="text"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                    </div>

                    <div>
                        <label for="modal_role" class="block text-sm font-medium text-concierge-navy">Role</label>
                        <select id="modal_role" name="role" required
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            <option value="agent" selected>Agent</option>
                        </select>
                    </div>

                    <div>
                        <label for="modal_password" class="block text-sm font-medium text-concierge-navy">Password</label>
                        <div class="relative mt-1.5">
                            <input id="modal_password" name="password" type="password" required
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2.5 pl-4 pr-11 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            <button type="button"
                                class="absolute inset-y-0 right-0 flex cursor-pointer items-center rounded-r-xl px-3 text-concierge-muted hover:text-concierge-navy"
                                data-password-toggle="modal_password" aria-label="Show password" aria-pressed="false">
                                <span data-icon="show" class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </span>
                                <span data-icon="hide" class="hidden flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="modal_confirm_password" class="block text-sm font-medium text-concierge-navy">Confirm
                            password</label>
                        <div class="relative mt-1.5">
                            <input id="modal_confirm_password" name="confirm_password" type="password" required
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2.5 pl-4 pr-11 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                            <button type="button"
                                class="absolute inset-y-0 right-0 flex cursor-pointer items-center rounded-r-xl px-3 text-concierge-muted hover:text-concierge-navy"
                                data-password-toggle="modal_confirm_password" aria-label="Show password"
                                aria-pressed="false">
                                <span data-icon="show" class="flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </span>
                                <span data-icon="hide" class="hidden flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" data-close-agent-modal
                        class="flex-1 cursor-pointer rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-concierge-navy hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 cursor-pointer rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep">
                        Create agent
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if ($canManageAgents)
        {{-- Edit agent modal --}}
        <div id="edit-agent-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
            aria-hidden="true" role="dialog" aria-labelledby="edit-agent-modal-title">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 id="edit-agent-modal-title" class="text-lg font-semibold text-concierge-navy">Edit agent</h2>
                    <button type="button" data-close-edit-agent-modal
                        class="rounded-lg p-2 text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="edit-agent-form" method="POST" class="space-y-4 px-6 py-5">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="edit_modal_name"
                                class="block text-sm font-medium text-concierge-navy">Name</label>
                            <input id="edit_modal_name" name="name" type="text" required autocomplete="name"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>

                        <div>
                            <label for="edit_modal_email"
                                class="block text-sm font-medium text-concierge-navy">Email</label>
                            <input id="edit_modal_email" name="email" type="email" required autocomplete="email"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>

                        <div>
                            <label for="edit_modal_phone_number"
                                class="block text-sm font-medium text-concierge-navy">Phone number</label>
                            <input id="edit_modal_phone_number" name="phone_number" type="text" required
                                autocomplete="tel"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>

                        <div>
                            <label for="edit_modal_agent_cnic" class="block text-sm font-medium text-concierge-navy">Agent
                                CNIC</label>
                            <input id="edit_modal_agent_cnic" name="agent_cnic" type="text"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>

                        <div class="col-span-full">
                            <label for="edit_modal_home_address"
                                class="block text-sm font-medium text-concierge-navy">Home Address</label>
                            <textarea id="edit_modal_home_address" name="home_address" rows="3"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20"></textarea>
                        </div>

                        <div>
                            <label for="edit_modal_guardian_name"
                                class="block text-sm font-medium text-concierge-navy">Guardian Name</label>
                            <input id="edit_modal_guardian_name" name="guardian_name" type="text"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>

                        <div>
                            <label for="edit_modal_guardian_phone_number"
                                class="block text-sm font-medium text-concierge-navy">Guardian Phone Number</label>
                            <input id="edit_modal_guardian_phone_number" name="guardian_phone_number" type="text"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>

                        <div>
                            <label for="edit_modal_guardian_cnic"
                                class="block text-sm font-medium text-concierge-navy">Guardian CNIC</label>
                            <input id="edit_modal_guardian_cnic" name="guardian_cnic" type="text"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                        </div>

                        <div>
                            <label for="edit_modal_password" class="block text-sm font-medium text-concierge-navy">New
                                password <span class="font-normal text-concierge-muted">(optional)</span></label>
                            <div class="relative mt-1.5">
                                <input id="edit_modal_password" name="password" type="password"
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2.5 pl-4 pr-11 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                                <button type="button"
                                    class="absolute inset-y-0 right-0 flex cursor-pointer items-center rounded-r-xl px-3 text-concierge-muted hover:text-concierge-navy"
                                    data-password-toggle="edit_modal_password" aria-label="Show password"
                                    aria-pressed="false">
                                    <span data-icon="show" class="flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>
                                    <span data-icon="hide" class="hidden flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="edit_modal_confirm_password"
                                class="block text-sm font-medium text-concierge-navy">Confirm new password</label>
                            <div class="relative mt-1.5">
                                <input id="edit_modal_confirm_password" name="confirm_password" type="password"
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/80 py-2.5 pl-4 pr-11 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                                <button type="button"
                                    class="absolute inset-y-0 right-0 flex cursor-pointer items-center rounded-r-xl px-3 text-concierge-muted hover:text-concierge-navy"
                                    data-password-toggle="edit_modal_confirm_password" aria-label="Show password"
                                    aria-pressed="false">
                                    <span data-icon="show" class="flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </span>
                                    <span data-icon="hide" class="hidden flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"
                                            aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" data-close-edit-agent-modal
                            class="flex-1 cursor-pointer rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-concierge-navy hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 cursor-pointer rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Permissions modal --}}
        <div id="permissions-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4"
            aria-hidden="true" role="dialog" aria-labelledby="permissions-modal-title">
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 id="permissions-modal-title" class="text-lg font-semibold text-concierge-navy">Permissions</h2>
                    <button type="button" data-close-permissions-modal
                        class="rounded-lg p-2 text-concierge-muted hover:bg-slate-100 hover:text-concierge-navy"
                        aria-label="Close">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="permissions-form" class="px-6 py-5">
                    @csrf
                    <p id="permissions-agent-label" class="mb-4 text-sm text-concierge-muted"></p>
                    <div id="permissions-checkboxes" class="space-y-3"></div>
                    <div class="mt-6 flex gap-3">
                        <button type="button" data-close-permissions-modal
                            class="flex-1 cursor-pointer rounded-xl border border-slate-200 py-2.5 text-sm font-medium text-concierge-navy hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="flex-1 cursor-pointer rounded-xl bg-concierge-navy py-2.5 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 hover:bg-concierge-navy-deep">
                            Save permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @vite(['resources/js/agents.js'])
@endpush
