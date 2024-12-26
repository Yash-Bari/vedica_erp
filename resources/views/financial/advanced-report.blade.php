@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Advanced Financial Report</h1>

        {{-- Filtering Form --}}
        <form id="advancedReportForm" method="GET" action="{{ route('financial.advanced-report') }}" class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Date Range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" 
                           value="{{ request('start_date') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" 
                           value="{{ request('end_date') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                {{-- Platform Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Platforms</label>
                    <select name="platforms[]" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="platformSelect">
                        {{-- Options will be dynamically populated --}}
                    </select>
                </div>

                {{-- Revenue Range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Revenue</label>
                    <input type="number" name="min_revenue" 
                           value="{{ request('min_revenue') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Revenue</label>
                    <input type="number" name="max_revenue" 
                           value="{{ request('max_revenue') }}" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>

                {{-- Project Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Project Status</label>
                    <select name="project_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Statuses</option>
                        <option value="Completed" {{ request('project_status') == 'Completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                        <option value="In Progress" {{ request('project_status') == 'In Progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="Pending" {{ request('project_status') == 'Pending' ? 'selected' : '' }}>
                            Pending
                        </option>
                    </select>
                </div>

                {{-- Expense Categories --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Expense Categories</label>
                    <select name="expense_categories[]" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" id="expenseCategorySelect">
                        {{-- Options will be dynamically populated --}}
                    </select>
                </div>

                {{-- Export Format --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Export Format</label>
                    <select name="export_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">View Report</option>
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                        <option value="xlsx">Excel</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="reset" class="btn btn-secondary">Reset</button>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </form>

        {{-- Results Summary --}}
        @if($projects->count() > 0 || $expenses->count() > 0)
        <div class="mt-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-100 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800">Total Projects</h3>
                    <p class="text-2xl font-bold text-blue-900">
                        {{ $projects->count() }}
                    </p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800">Total Project Revenue</h3>
                    <p class="text-2xl font-bold text-green-900">
                        ₹{{ number_format($projects->sum('total_revenue'), 2) }}
                    </p>
                </div>
                <div class="bg-red-100 p-4 rounded-lg">
                    <h3 class="text-lg font-semibold text-red-800">Total Expenses</h3>
                    <p class="text-2xl font-bold text-red-900">
                        ₹{{ number_format($expenses->sum('amount'), 2) }}
                    </p>
                </div>
            </div>

            {{-- Projects Table --}}
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Projects</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Platform
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total Revenue
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($projects as $project)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $project->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $project->platform }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    ₹{{ number_format($project->total_revenue, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $project->status }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Expenses Table --}}
            <div>
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Expenses</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($expenses as $expense)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $expense->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $expense->category }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600">
                                    ₹{{ number_format($expense->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $expense->date->format('Y-m-d') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="text-center py-8 text-gray-500">
                No results found. Try adjusting your filters.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch filter options
    fetch('{{ route('financial.filter-options') }}')
        .then(response => response.json())
        .then(data => {
            // Populate platform select
            const platformSelect = document.getElementById('platformSelect');
            data.platforms.forEach(platform => {
                const option = document.createElement('option');
                option.value = platform;
                option.textContent = platform;
                option.selected = {{ json_encode(request('platforms', [])) }}.includes(platform);
                platformSelect.appendChild(option);
            });

            // Populate expense category select
            const expenseCategorySelect = document.getElementById('expenseCategorySelect');
            data.expense_categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                option.selected = {{ json_encode(request('expense_categories', [])) }}.includes(category);
                expenseCategorySelect.appendChild(option);
            });

            // Enable multiple select
            $(platformSelect).select2();
            $(expenseCategorySelect).select2();
        });
});
</script>
@endpush
