@extends('layouts.app')
@section('title', 'Domains')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Domains</h1>
        <a href="{{ route('domains.create') }}"
           class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            + Add Domain
        </a>
    </div>

    @if($domains->isEmpty())
        <div class="text-center py-16 bg-white rounded-lg border">
            <p class="text-gray-500">No domains yet. Add your first one!</p>
        </div>
    @else
        <div class="bg-white shadow-sm rounded-lg border overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Interval</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Check</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($domains as $domain)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            @if($domain->is_up === true)
                                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block animate-pulse"></span>UP
                                </span>
                            @elseif($domain->is_up === false)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>DOWN
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                                    PENDING
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $domain->display_name }}</div>
                            @if($domain->name)
                                <div class="text-sm text-gray-400">{{ $domain->url }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $domain->check_method }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">every {{ $domain->check_interval }}m</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $domain->last_checked_at ? $domain->last_checked_at->diffForHumans() : 'Never' }}
                            @if($domain->last_response_time)
                                <span class="text-gray-400">· {{ $domain->last_response_time }}ms</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                            <a href="{{ route('domains.logs', $domain) }}" class="text-gray-500 hover:text-gray-700">Logs</a>

                            <form method="POST" action="{{ route('domains.check-now', $domain) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-indigo-500 hover:text-indigo-700">Check now</button>
                            </form>

                            <a href="{{ route('domains.edit', $domain) }}" class="text-indigo-500 hover:text-indigo-700">Edit</a>

                            <form method="POST" action="{{ route('domains.destroy', $domain) }}" class="inline"
                                  onsubmit="return confirm('Delete this domain and all its logs?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $domains->links() }}
    @endif
</div>
@endsection
