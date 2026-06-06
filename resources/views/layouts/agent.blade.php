<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Concierge') — {{ config('app.name', 'CRM') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/agent-notifications-poller.js', 'resources/js/agent-web-push.js'])
</head>
<body class="@unless(request()->routeIs('agent.dashboard')) concierge-sidebar-drawer @endunless min-h-screen bg-concierge-page font-sans text-slate-800 antialiased @yield('body_class')">
    <div
        id="admin-sidebar-overlay"
        class="admin-sidebar-overlay fixed inset-0 z-[100] bg-slate-900/55 backdrop-blur-[2px] lg:hidden"
        aria-hidden="true"
        aria-label="Close menu"
    ></div>

    <div class="flex min-h-screen">
        @include('partials.agent.sidebar')

        <div class="concierge-layout-content">
            @include('partials.admin.navbar')

            <main class="flex-1 overflow-auto p-6 lg:p-8">
                @include('partials.agent.push-alerts-banner')
                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
