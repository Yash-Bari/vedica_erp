@extends('layouts.app')

@section('title', 'Salary Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        {{-- Header Section --}}
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Salary Management</h1>
            
            <div class="flex space-x-4">
                @can('process', App\Models\SalaryPayment::class)
                <button onclick="showBulkProcessModal()" 
                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <i class="fas fa-money-bill-wave mr-2"></i>Process Monthly Salaries
                </button>
                @endcan

                @can('create', App\Models\SalaryStructure::class)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-cog mr-2"></i>Salary Actions
                    </button>
                    
                    <div x-show="open" @click.away="open = false" 
                         class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-md shadow-lg z-20">
                        <a href="{{ route('salaries.structure.index') }}" 
                           class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                            <i class="fas fa-list mr-2"></i>View Salary Structures
                        </a>
                        <a href="{{ route('salaries.payments.index') }}" 
                           class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                            <i class="fas fa-history mr-2"></i>Payment History
                        </a>
                    </div>
                </div>
                @endcan
            </div>
        </div>

        {{-- Filters Section --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" action="{{ route('salaries.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>

        {{-- Summary Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="bg-white p-4 rounded-md shadow">
                <h3 class="text-sm font-medium text-gray-500">Total Employees</h3>
                <p class="text-2xl font-bold text-blue-600">{{ $totalEmployees }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $activeEmployees }} active employees</p>
            </div>
            
            <div class="bg-white p-4 rounded-md shadow">
                <h3 class="text-sm font-medium text-gray-500">Pending Payments</h3>
                <p class="text-2xl font-bold text-yellow-600">{{ $pendingPayments }}</p>
                <p class="text-xs text-gray-500 mt-1">Salaries pending for this month</p>
            </div>
            
            <div class="bg-white p-4 rounded-md shadow">
                <h3 class="text-sm font-medium text-gray-500">Processed Payments</h3>
                <p class="text-2xl font-bold text-green-600">{{ $processedPayments }}</p>
                <p class="text-xs text-gray-500 mt-1">Salaries processed this month</p>
            </div>
            
            <div class="bg-white p-4 rounded-md shadow">
                <h3 class="text-sm font-medium text-gray-500">Monthly Payroll</h3>
                <p class="text-2xl font-bold text-indigo-600">₹{{ number_format($totalMonthlyPayroll, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">Total monthly salary budget</p>
            </div>
        </div>

        {{-- Employees Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department/Role</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Basic Salary</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Allowances</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Deductions</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" 
                                         src="{{ $employee->profile_picture ?? asset('images/default-avatar.png') }}" 
                                         alt="{{ $employee->name }}">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $employee->employee_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $employee->department }}</div>
                            <div class="text-sm text-gray-500">{{ $employee->role }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($employee->activeSalaryStructure)
                                <div class="text-sm text-gray-900">₹{{ number_format($employee->activeSalaryStructure->basic_salary, 2) }}</div>
                                <div class="text-xs text-gray-500">Basic Salary</div>
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($employee->activeSalaryStructure)
                                <div class="text-sm text-gray-900">₹{{ number_format($employee->activeSalaryStructure->calculateTotalAllowances(), 2) }}</div>
                                <div class="text-xs text-gray-500">Total Allowances</div>
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($employee->activeSalaryStructure)
                                <div class="text-sm text-gray-900">₹{{ number_format($employee->activeSalaryStructure->calculateTotalDeductionsFromJson(), 2) }}</div>
                                <div class="text-xs text-gray-500">Total Deductions</div>
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($employee->activeSalaryStructure)
                                <div class="text-sm font-medium text-gray-900">₹{{ number_format($employee->activeSalaryStructure->calculateNetSalaryFromJson(), 2) }}</div>
                                <div class="text-xs text-gray-500">Net Salary</div>
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @php
                                $paymentStatus = $employee->getCurrentMonthPaymentStatus();
                                $statusColors = [
                                    'Paid' => 'bg-green-100 text-green-800',
                                    'Processing' => 'bg-yellow-100 text-yellow-800',
                                    'Pending' => 'bg-gray-100 text-gray-800',
                                    'Failed' => 'bg-red-100 text-red-800'
                                ];
                                $statusColor = $statusColors[$paymentStatus] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                {{ $paymentStatus }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center space-x-2">
                                @if($employee->activeSalaryStructure)
                                    @if($employee->hasPendingPayment())
                                        <a href="{{ route('salaries.payments.process', $employee) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">
                                            Process Payment
                                        </a>
                                    @else
                                        @php
                                            $payment = $employee->getCurrentMonthPayment();
                                        @endphp
                                        @if($payment)
                                            <a href="{{ route('salaries.payments.show', $payment) }}" 
                                               class="text-green-600 hover:text-green-900">
                                                View Receipt
                                            </a>
                                        @endif
                                    @endif
                                @else
                                    <a href="{{ route('salaries.structure.create', ['employee' => $employee->id]) }}" 
                                       class="text-yellow-600 hover:text-yellow-900">
                                        <i class="fas fa-plus-circle mr-1"></i>Add Salary Structure
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No employees found matching the criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 bg-white border-t border-gray-200">
            {{ $employees->appends(request()->input())->links() }}
        </div>
    </div>
</div>

{{-- Bulk Process Modal --}}
<div x-data="{ showBulkModal: false }" 
     x-show="showBulkModal" 
     class="fixed inset-0 z-50 overflow-y-auto" 
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Process All Pending Payments
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-4">
                                This will process salary payments for {{ $pendingPayments }} employees.
                            </p>
                            
                            <!-- Summary -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-medium text-gray-700 mb-2">Summary</h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex justify-between">
                                        <span>Employees to Process:</span>
                                        <span class="font-medium">{{ $pendingPayments }}</span>
                                    </li>
                                    <li class="flex justify-between mt-1">
                                        <span>Total Amount:</span>
                                        <span class="font-medium">₹{{ number_format($pendingPayrollAmount, 2) }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Warning -->
                            @if($pendingPayments > 0)
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            This action cannot be undone. Please verify the details before proceeding.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal footer -->
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                @can('processBulk', App\Models\SalaryPayment::class)
                    @if($pendingPayments > 0)
                    <button type="button" 
                            id="bulk-process-btn"
                            onclick="processBulkSalaries()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Process {{ $pendingPayments }} Payments
                    </button>
                    @endif
                @endcan
                <button type="button" 
                        @click="showBulkModal = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
function showBulkProcessModal() {
    Alpine.data('modalData', () => ({
        showBulkModal: true
    }));
}

// Function to handle bulk salary processing
function processBulkSalaries() {
    if (!confirm('Are you sure you want to process salaries for all pending employees?')) {
        return;
    }

    // Show loading state
    const button = document.querySelector('#bulk-process-btn');
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to process payments');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', error.message || 'Failed to process payments. Please try again.');
    })
    .finally(() => {
        // Reset button state
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Function to show notifications
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>

@endsection

@push('scripts')
<script>
function showBulkProcessModal() {
    Alpine.data('modalData', () => ({
        showBulkModal: true
    }));
}

function processSalary(employeeId) {
    if (!confirm('Are you sure you want to process salary for this employee?')) {
        return;
    }

    window.location.href = `{{ url('salaries/process') }}/${employeeId}`;
}

function processBulkSalaries() {
    if (!confirm('Are you sure you want to process salaries for all pending employees?')) {
        return;
    }

    
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Salaries processed successfully!');
            window.location.reload();
        } else {
            alert('Failed to process salaries: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing salaries');
    });
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
