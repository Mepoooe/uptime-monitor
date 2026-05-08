@extends('layouts.app')
@section('title', 'Logs – ' . $domain->display_name)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <div class="flex items-center gap-3">
                @if($domain->is_up === true)
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                        <span class="w-2 h-2 rounded-full bg-green-500 inline-block animate-pulse"></span>UP
                    </span>
                @elseif($domain->is_up === false)
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1 text-sm font-medium text-red-800">
                        <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>DOWN
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-500">PENDING</span>
                @endif
                <h1 class="text-2xl font-bold text-gray-900">{{ $domain->display_name }}</h1>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                {{ $domain->url }} · {{ $domain->check_method }} · every {{ $domain->check_interval }}min · timeout {{ $domain->check_timeout }}s
            </p>
        </div>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('domains.check-now', $domain) }}">
                @csrf
                <button type="submit"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Check Now
                </button>
            </form>
            <a href="{{ route('domains.edit', $domain) }}"
               class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                Edit
            </a>
        </div>
    </div>

    {{-- Log table --}}
    @if($logs->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg border">
            <p class="text-gray-500">No check logs yet.</p>
        </div>
    @else
        <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Result</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Checked At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Response Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($logs as $log)
                    <tr class="{{ $log->is_up ? '' : 'bg-red-50' }}">
                        <td class="px-6 py-3 whitespace-nowrap">
                            @if($log->is_up)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">✓ UP</span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">✗ DOWN</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-700 whitespace-nowrap">
                            {{ $log->checked_at->format('Y-m-d H:i:s') }}
                            <span class="text-gray-400 text-xs ml-1">({{ $log->checked_at->diffForHumans() }})</span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600">
                            {{ $log->status_code ?? '—' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600">
                            {{ $log->response_time ? $log->response_time . ' ms' : '—' }}
                        </td>
                        <td class="px-6 py-3 text-sm text-red-600 max-w-xs truncate">
                            {{ $log->error ?? '' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div>{{ $logs->links() }}</div>
    @endif

    <a href="{{ route('domains.index') }}" class="text-sm text-indigo-600 hover:underline">← Back to domains</a>
</div>
@endsection
