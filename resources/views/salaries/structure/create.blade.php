@extends('layouts.app')

@section('title', 'Create Salary Structure')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="border-b border-gray-200 px-6 py-4">
        <h3 class="text-xl font-semibold text-gray-800">Create Salary Structure</h3>
    </div>

    @can('create', App\Models\SalaryStructure::class)
    <form action="{{ route('salaries.structure.store') }}" method="POST">
        @csrf
        <div class="p-6">
            <!-- Employee Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <select name="employee_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">
                            {{ $employee->full_name }} ({{ $employee->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Earnings Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Base Salary</label>
                    <input type="number" name="base_salary" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">House Rent Allowance</label>
                    <input type="number" name="house_rent_allowance" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Conveyance Allowance</label>
                    <input type="number" name="conveyance_allowance" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0" step="0.01">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Medical Allowance</label>
                    <input type="number" name="medical_allowance" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Performance Bonus</label>
                    <input type="number" name="performance_bonus" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0" step="0.01">
                </div>
            </div>

            <!-- Deductions Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Provident Fund</label>
                    <input type="number" name="provident_fund" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Professional Tax</label>
                    <input type="number" name="professional_tax" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Other Deductions</label>
                    <input type="number" name="other_deductions" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" min="0" step="0.01">
                </div>
            </div>

            <!-- Net Salary Preview -->
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <span class="font-medium">Net Salary Preview:</span>
                            <span id="net-salary-preview" class="ml-1">0.00</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                Create Salary Structure
            </button>
            <a href="{{ route('salaries.structure.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                Cancel
            </a>
        </div>
    </form>
    @else
    <div class="p-6">
        <div class="bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        You are not authorized to create salary structures.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Alpine.js components if needed

        // Real-time net salary calculation
        const numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('input', calculateNetSalary);
        });

        function calculateNetSalary() {
            const baseSalary = parseFloat(document.querySelector('input[name="base_salary"]').value) || 0;
            const houseRent = parseFloat(document.querySelector('input[name="house_rent_allowance"]').value) || 0;
            const conveyance = parseFloat(document.querySelector('input[name="conveyance_allowance"]').value) || 0;
            const medical = parseFloat(document.querySelector('input[name="medical_allowance"]').value) || 0;
            const performanceBonus = parseFloat(document.querySelector('input[name="performance_bonus"]').value) || 0;
            
            const providentFund = parseFloat(document.querySelector('input[name="provident_fund"]').value) || 0;
            const professionalTax = parseFloat(document.querySelector('input[name="professional_tax"]').value) || 0;
            const otherDeductions = parseFloat(document.querySelector('input[name="other_deductions"]').value) || 0;
            
            const totalEarnings = baseSalary + houseRent + conveyance + medical + performanceBonus;
            const totalDeductions = providentFund + professionalTax + otherDeductions;
            const netSalary = totalEarnings - totalDeductions;
            
            document.getElementById('net-salary-preview').textContent = netSalary.toFixed(2);
        }
    });
</script>
@endpush