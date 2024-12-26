@extends('layouts.app')

@section('title', 'Salary Management')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        {{-- Header Section --}}
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Salary Management</h1>
            
            @can('create', App\Models\SalaryStructure::class)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-cog mr-2"></i>Salary Actions
                </button>
                
                <div x-show="open" @click.away="open = false" 
                     class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-md shadow-lg z-20">
                    <a href="{{ route('salaries.structure.create') }}" 
                       class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-plus mr-2"></i>Create Salary Structure
                    </a>
                    <a href="{{ route('salaries.structure.index') }}" 
                       class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-list mr-2"></i>View Salary Structures
                    </a>
                </div>
            </div>
            @endcan
        </div>

        {{-- Filters Section --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" action="{{ route('salaries.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                {{ $role }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status</label>
                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('salaries.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                        <i class="fas fa-sync mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Summary Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="bg-white p-4 rounded-md shadow">
                <h3 class="text-sm font-medium text-gray-500">Total Employees</h3>
                <p class="text-2xl font-bold text-blue-600">
                    {{ $employees->total() }}
                </p>
            </div>
            <div class="bg-white p-4 rounded-md shadow">
                <h3 class="text-sm font-medium text-gray-500">Employees with Salary Structure</h3>
                <p class="text-2xl font-bold text-green-600">
                    {{ $employeesWithSalaryStructure }}
                </p>
            </div>
            <div class="bg-white p-4 rounded-md shadow">
                <h3 class="text-sm font-medium text-gray-500">Active Employees</h3>
                <p class="text-2xl font-bold text-yellow-600">
                    {{ $activeEmployees }}
                </p>
            </div>
            <div class="bg-white p-4 rounded-md shadow">
                <h3 class="text-sm font-medium text-gray-500">Total Monthly Payroll</h3>
                <p class="text-2xl font-bold text-green-800">
                    ₹{{ number_format($totalMonthlyPayroll, 2) }}
                </p>
            </div>
        </div>

        {{-- Employees with Salary Structures Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Base Salary</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Allowances</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Deductions</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($employees as $employee)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" src="{{ $employee->profile_picture ?? asset('images/default-avatar.png') }}" alt="{{ $employee->name }}">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $employee->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $employee->role }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($employee->activeSalaryStructure)
                                <div class="text-sm text-gray-900">{{ number_format($employee->activeSalaryStructure->base_salary, 2) }}</div>
                            @else
                                <div class="text-sm text-gray-500">No Active Salary Structure</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($employee->activeSalaryStructure)
                                <div class="text-sm text-gray-900">{{ number_format($employee->activeSalaryStructure->house_rent_allowance + $employee->activeSalaryStructure->conveyance_allowance + $employee->activeSalaryStructure->medical_allowance, 2) }}</div>
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($employee->activeSalaryStructure)
                                <div class="text-sm text-gray-900">{{ number_format($employee->activeSalaryStructure->provident_fund + $employee->activeSalaryStructure->professional_tax, 2) }}</div>
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            @if($employee->activeSalaryStructure)
                                <div class="text-sm text-gray-900">{{ number_format($employee->activeSalaryStructure->calculateNetSalary(), 2) }}</div>
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($employee->activeSalaryStructure)
                                @php
                                    $salaryProcessed = \App\Models\SalaryPayment::processedThisMonth($employee->id)->exists();
                                @endphp

                                @if(!$salaryProcessed)
                                    <a href="{{ route('salaries.process', $employee->id) }}" 
                                       class="text-blue-600 hover:text-blue-900">
                                        Process Payment
                                    </a>
                                @else
                                    @php
                                        $existingReceipt = \App\Models\SalaryPayment::processedThisMonth($employee->id)->first()->salaryReceipt;
                                    @endphp
                                    @can('view', $existingReceipt)
                                        <a href="{{ route('salaries.receipts.show', $existingReceipt->id) }}" 
                                           class="text-green-600 hover:text-green-900">
                                            View Receipt
                                        </a>
                                    @else
                                        <span class="text-warning">Receipt Access Restricted</span>
                                    @endcan
                                @endif
                            @else
                                <span class="text-gray-400 cursor-not-allowed">No Salary Structure</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No employees found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 bg-white border-t border-gray-200">
            {{ $employees->appends(request()->input())->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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
