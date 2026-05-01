@extends('layouts.agent')

@section('title', 'Notifications')

@section('content')
    <div class="mx-auto max-w-5xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-concierge-navy lg:text-3xl">Notifications</h1>
            <p class="mt-1 text-concierge-muted">Recent lead assignment updates.</p>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <ul class="divide-y divide-slate-100">
                @forelse ($notifications as $notification)
                    @php
                        $data = $notification->data ?? [];
                        $title = (string) ($data['title'] ?? 'Notification');
                        $message = (string) ($data['message'] ?? '');
                        $type = (string) ($data['type'] ?? '');
                        $customerName = (string) ($data['customer_name'] ?? '');
                    @endphp
                    <li class="px-5 py-4 sm:px-6">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="inline-flex items-center gap-2 font-semibold text-concierge-navy">
                                    @if ($notification->read_at === null)
                                        <span class="inline-flex h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"
                                            aria-hidden="true"></span>
                                    @endif
                                    <span>{{ $title }}</span>
                                </p>
                                @if ($message !== '')
                                    <p class="mt-1 text-sm text-concierge-muted">
                                        @if ($customerName !== '')
                                            @if ($type === 'lead_reassigned')
                                                Lead for <span class="font-semibold text-concierge-navy">{{ $customerName }}</span>
                                                has been reassigned to you.
                                            @else
                                                A new lead for <span class="font-semibold text-concierge-navy">{{ $customerName }}</span>
                                                has been assigned to you.
                                            @endif
                                        @else
                                            {{ $message }}
                                        @endif
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('agent.notifications.open', ['notificationId' => $notification->id]) }}"
                                class="shrink-0 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-concierge-navy transition hover:bg-slate-50">
                                View
                            </a>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-10 text-center text-sm text-concierge-muted">
                        No notifications yet.
                    </li>
                @endforelse
            </ul>
            @if ($notifications->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
