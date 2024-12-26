@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Expense Management</h1>
        
        @can('create', App\Models\Expense::class)
        <a href="{{ route('expenses.create') }}" class="btn btn-primary flex items-center">
            <x-heroicon-o-plus-circle class="w-5 h-5 mr-2" />
            Create New Expense
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <form action="{{ route('expenses.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Project</label>
                <select name="project_id" class="mt-1 block w-full rounded-md border-gray-300">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" 
                            {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Expense Type</label>
                <select name="type" class="mt-1 block w-full rounded-md border-gray-300">
                    <option value="">All Types</option>
                    @foreach(['Material', 'Labor', 'Equipment', 'Transportation', 'Miscellaneous'] as $type)
                        <option value="{{ $type }}" 
                            {{ request('type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Date From</label>
                <input type="date" name="start_date" 
                    value="{{ request('start_date') }}" 
                    class="mt-1 block w-full rounded-md border-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Date To</label>
                <input type="date" name="end_date" 
                    value="{{ request('end_date') }}" 
                    class="mt-1 block w-full rounded-md border-gray-300">
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="btn btn-secondary mr-2">Apply Filters</button>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline">Reset</a>
        </div>
    </form>
    </div>

    {{-- Expenses Table --}}
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Project
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Amount
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($expenses as $expense)
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $expense->date->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $expense->project ? $expense->project->name : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $typeClass = match($expense->type) {
                                'Material' => 'bg-blue-100 text-blue-800',
                                'Labor' => 'bg-green-100 text-green-800',
                                'Equipment' => 'bg-yellow-100 text-yellow-800',
                                'Transportation' => 'bg-purple-100 text-purple-800',
                                'Miscellaneous' => 'bg-pink-100 text-pink-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $typeClass }}">
                            {{ $expense->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                        ₹{{ number_format($expense->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            @can('view', $expense)
                            <a href="{{ route('expenses.show', $expense) }}" 
                                class="text-blue-600 hover:text-blue-900">
                                <x-heroicon-o-eye class="w-5 h-5" />
                            </a>
                            @endcan

                            @can('update', $expense)
                            <a href="{{ route('expenses.edit', $expense) }}" 
                                class="text-yellow-600 hover:text-yellow-900">
                                <x-heroicon-o-pencil class="w-5 h-5" />
                            </a>
                            @endcan

                            @can('delete', $expense)
                            <form action="{{ route('expenses.destroy', $expense) }}" 
                                method="POST" 
                                onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <x-heroicon-o-trash class="w-5 h-5" />
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No expenses found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $expenses->appends(request()->query())->links() }}
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white shadow rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Total Expenses</h3>
            <p class="text-2xl font-bold text-blue-600">
                ₹{{ number_format($expenses->sum('amount'), 2) }}
            </p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Avg. Expense</h3>
            <p class="text-2xl font-bold text-green-600">
                ₹{{ number_format($expenses->avg('amount'), 2) }}
            </p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Expense Count</h3>
            <p class="text-2xl font-bold text-purple-600">
                {{ $expenses->count() }}
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.querySelector('form');
        filterForm.addEventListener('submit', function(e) {
            // Optional: Add client-side validation or loading indicator
        });
    });
</script>
@endpush
