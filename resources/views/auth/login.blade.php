<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in — {{ config('app.name', 'NAZIRSONS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-concierge-page font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <div class="mb-10 text-center">
            <p class="text-2xl font-bold tracking-tight text-concierge-navy">NAZIRSONS</p>
        </div>

        <div class="w-full max-w-md rounded-2xl border border-slate-200/80 bg-white p-8 shadow-lg shadow-slate-200/50">
            <h1 class="text-lg font-semibold text-concierge-navy">Sign in</h1>
            <p class="mt-1 text-sm text-concierge-muted">Use the email and password provided for your account.</p>

            <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-concierge-navy">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" autofocus
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20 @error('email') border-rose-300 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-concierge-navy">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm focus:border-concierge-accent focus:bg-white focus:outline-none focus:ring-2 focus:ring-concierge-accent/20">
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" name="remember" type="checkbox" value="1" class="rounded border-slate-300 text-concierge-accent focus:ring-concierge-accent/30">
                    <label for="remember" class="text-sm text-concierge-muted">Remember me</label>
                </div>

                <button type="submit"
                        class="w-full rounded-xl bg-concierge-navy py-3 text-sm font-semibold text-white shadow-md shadow-concierge-navy/25 transition hover:bg-concierge-navy-deep">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</body>
</html>
