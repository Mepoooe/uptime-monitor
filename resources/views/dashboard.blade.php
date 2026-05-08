@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <a href="{{ route('domains.create') }}"
           class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            + Add Domain
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg border p-5 text-center">
            <div class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Total</div>
        </div>
        <div class="bg-white rounded-lg border p-5 text-center">
            <div class="text-3xl font-bold text-green-600">{{ $stats['up'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Online</div>
        </div>
        <div class="bg-white rounded-lg border p-5 text-center">
            <div class="text-3xl font-bold text-red-600">{{ $stats['down'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Offline</div>
        </div>
        <div class="bg-white rounded-lg border p-5 text-center">
            <div class="text-3xl font-bold text-gray-400">{{ $stats['unknown'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Unknown</div>
        </div>
    </div>

    {{-- Domain list --}}
    @if($domains->isEmpty())
        <div class="text-center py-16 bg-white rounded-lg border">
            <p class="text-gray-500 text-lg">No domains yet.</p>
            <a href="{{ route('domains.create') }}"
               class="mt-4 inline-block text-indigo-600 hover:underline">Add your first domain →</a>
        </div>
    @else
        <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Check</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interval</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($domains as $domain)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($domain->is_up === true)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>UP
                                </span>
                            @elseif($domain->is_up === false)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>DOWN
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                    PENDING
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('domains.logs', $domain) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ $domain->display_name }}
                            </a>
                            @if(!$domain->is_active)
                                <span class="ml-2 text-xs text-gray-400">(paused)</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($domain->last_response_time)
                                {{ $domain->last_response_time }} ms
                                @if($domain->last_status_code)
                                    <span class="text-gray-400">· {{ $domain->last_status_code }}</span>
                                @endif
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $domain->last_checked_at ? $domain->last_checked_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            every {{ $domain->check_interval }}m
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</div>
@endsection
