@extends('layouts.app')

@section('title', 'Salary Payments')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        {{-- Header Section --}}
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Salary Payments</h1>
            
            <div class="flex space-x-4">
                @can('process', App\Models\SalaryPayment::class)
                <button onclick="showBulkProcessModal()" 
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-money-bill-wave mr-2"></i>Process Monthly Salaries
                </button>
                @endcan

                <a href="{{ route('salaries.index') }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">
                    Back to Salary Management
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <form action="{{ route('salaries.payments.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                    <select name="month" id="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Months</option>
                        @foreach(range(1, 12) as $month)
                            <option value="{{ $month }}" {{ request('month') == $month ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                    <select name="year" id="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Years</option>
                        @foreach(range(date('Y'), date('Y')-5) as $year)
                            <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        {{-- Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Total Payments</h3>
                <p class="text-3xl font-bold text-blue-600">{{ $totalPayments }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Current Month</h3>
                <p class="text-3xl font-bold text-green-600">{{ $currentMonthPayments }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Pending</h3>
                <p class="text-3xl font-bold text-yellow-600">{{ $pendingPayments }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-700">Total Amount Paid</h3>
                <p class="text-3xl font-bold text-indigo-600">₹{{ number_format($totalAmount, 2) }}</p>
            </div>
        </div>

        {{-- Payments Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Employee
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Period
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Net Salary
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Payment Date
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments as $payment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $payment->employee->first_name }} {{ $payment->employee->last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $payment->employee->employee_code }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    {{ DateTime::createFromFormat('!m', $payment->month)->format('F') }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $payment->year }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    ₹{{ number_format($payment->net_salary, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $payment->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $payment->status === 'processing' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $payment->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $payment->payment_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('salaries.payments.show', $payment) }}" 
                                   class="text-blue-600 hover:text-blue-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                No salary payments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
function processSalary(paymentId) {
    if (!confirm('Are you sure you want to process this salary payment?')) {
        return;
    }

    window.location.href = `{{ url('salaries/payments') }}/${paymentId}/process`;
}

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.querySelector('form');
    const resetButton = filterForm.querySelector('a');

    resetButton.addEventListener('click', function(e) {
        e.preventDefault();
        filterForm.reset();
        window.location.href = this.href;
    });
});
</script>
@endpush
@endsection
