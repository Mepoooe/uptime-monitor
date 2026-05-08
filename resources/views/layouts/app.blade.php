<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-full">
    {{-- Navbar --}}
    <nav class="bg-indigo-600 shadow">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-white font-bold text-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Uptime Monitor
                    </a>
                    <div class="hidden md:flex gap-4">
                        <a href="{{ route('dashboard') }}"
                           class="text-indigo-100 hover:text-white text-sm font-medium
                                  {{ request()->routeIs('dashboard') ? 'text-white underline' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('domains.index') }}"
                           class="text-indigo-100 hover:text-white text-sm font-medium
                                  {{ request()->routeIs('domains.*') ? 'text-white underline' : '' }}">
                            Domains
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-indigo-200 text-sm">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-indigo-100 hover:text-white text-sm font-medium">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border-b border-green-200 px-4 py-3">
            <div class="mx-auto max-w-7xl">
                <p class="text-green-800 text-sm">✓ {{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-b border-red-200 px-4 py-3">
            <div class="mx-auto max-w-7xl">
                <p class="text-red-800 text-sm">✗ {{ session('error') }}</p>
            </div>
        </div>
    @endif

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</div>

</body>
</html>
