@extends('layouts.app')
@section('title', 'Add Domain')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Domain</h1>

    <div class="bg-white shadow-sm rounded-lg border p-6">
        <form method="POST" action="{{ route('domains.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL <span class="text-red-500">*</span></label>
                <input type="url" name="url" value="{{ old('url') }}" required
                       placeholder="https://example.com"
                       class="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500
                              @error('url') border-red-400 @enderror">
                @error('url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name (optional)</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       placeholder="My Website"
                       class="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check Method</label>
                    <select name="check_method"
                            class="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="HEAD" {{ old('check_method', 'HEAD') === 'HEAD' ? 'selected' : '' }}>HEAD</option>
                        <option value="GET"  {{ old('check_method') === 'GET' ? 'selected' : '' }}>GET</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">HEAD is faster</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interval (minutes)</label>
                    <select name="check_interval"
                            class="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($intervals as $interval)
                            <option value="{{ $interval }}" {{ old('check_interval', 5) == $interval ? 'selected' : '' }}>
                                every {{ $interval }} min
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timeout (seconds)</label>
                    <input type="number" name="check_timeout" value="{{ old('check_timeout', 10) }}"
                           min="1" max="60"
                           class="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       {{ old('is_active', true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="text-sm text-gray-700">Active (monitoring enabled)</label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    Add Domain
                </button>
                <a href="{{ route('domains.index') }}"
                   class="rounded-md border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
