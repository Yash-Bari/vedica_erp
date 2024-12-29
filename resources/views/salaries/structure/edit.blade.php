@extends('layouts.app')

@section('title', 'Edit Salary Structure')

@section('content')
<div class="container mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-700 mb-4">Edit Salary Structure</h1>
        
        @if ($errors->any())
        <div class="mb-4 bg-red-50 text-red-800 p-4 rounded-md">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        
        @can('update', $structure)
        <form action="{{ route('salaries.structure.update', $structure->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Employee Information -->
            <div class="bg-gray-50 p-4 rounded-md mb-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Employee Information</h2>
                <p><strong>Name:</strong> {{ $employee->full_name }}</p>
                <p><strong>Employee Code:</strong> {{ $employee->employee_code }}</p>
                <p><strong>Role:</strong> {{ $employee->role }}</p>
            </div>

            <!-- Base Pay Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Base Salary</label>
                    <input type="number" name="base_salary" 
                           value="{{ old('base_salary', $structure->base_salary) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('base_salary') border-red-500 @enderror" 
                           required min="0" step="0.01">
                    @error('base_salary')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Hourly Rate</label>
                    <input type="number" name="hourly_rate" 
                           value="{{ old('hourly_rate', $structure->hourly_rate) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('hourly_rate') border-red-500 @enderror" 
                           required min="0" step="0.01">
                    @error('hourly_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Overtime Rate</label>
                    <input type="number" name="overtime_rate" 
                           value="{{ old('overtime_rate', $structure->overtime_rate) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('overtime_rate') border-red-500 @enderror" 
                           required min="0" step="0.01">
                    @error('overtime_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Bonus Percentage</label>
                    <input type="number" name="bonus_percentage" 
                           value="{{ old('bonus_percentage', $structure->bonus_percentage) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('bonus_percentage') border-red-500 @enderror" 
                           required min="0" max="100" step="0.01">
                    @error('bonus_percentage')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Allowances Section -->
            <h2 class="text-lg font-semibold text-gray-700 mb-4 mt-8">Allowances</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">House Rent Allowance</label>
                    <input type="number" name="house_rent" 
                           value="{{ old('house_rent', $allowances['house_rent'] ?? 0) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Conveyance Allowance</label>
                    <input type="number" name="conveyance" 
                           value="{{ old('conveyance', $allowances['conveyance'] ?? 0) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Medical Allowance</label>
                    <input type="number" name="medical" 
                           value="{{ old('medical', $allowances['medical'] ?? 0) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Performance Bonus</label>
                    <input type="number" name="performance_bonus" 
                           value="{{ old('performance_bonus', $allowances['performance_bonus'] ?? 0) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
            </div>

            <!-- Deductions Section -->
            <h2 class="text-lg font-semibold text-gray-700 mb-4 mt-8">Deductions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Provident Fund</label>
                    <input type="number" name="provident_fund" 
                           value="{{ old('provident_fund', $deductions['provident_fund'] ?? 0) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Professional Tax</label>
                    <input type="number" name="professional_tax" 
                           value="{{ old('professional_tax', $deductions['professional_tax'] ?? 0) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Other Deductions</label>
                    <input type="number" name="other" 
                           value="{{ old('other', $deductions['other'] ?? 0) }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
            </div>

            <!-- Net Salary Preview -->
            <div class="mt-8 bg-blue-50 p-6 rounded-lg">
                <h2 class="text-lg font-semibold text-blue-800 mb-4">Salary Summary</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-4 rounded-md shadow-sm">
                        <p class="text-gray-600 mb-1">Total Earnings</p>
                        <p class="text-xl font-bold text-blue-600">{{ number_format($totalEarnings ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-md shadow-sm">
                        <p class="text-gray-600 mb-1">Total Deductions</p>
                        <p class="text-xl font-bold text-red-600">{{ number_format($totalDeductions ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-md shadow-sm">
                        <p class="text-gray-600 mb-1">Net Salary</p>
                        <p class="text-xl font-bold text-green-600">{{ number_format($netSalary ?? 0, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('salaries.structure.show', $structure->id) }}" 
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                    Update Salary Structure
                </button>
            </div>
        </form>
        @else
        <div class="bg-red-100 text-red-800 p-4 rounded-md">
            You are not authorized to edit this salary structure.
        </div>
        @endcan
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input[type="number"]');
        const totalEarningsEl = document.getElementById('total-earnings');
        const totalDeductionsEl = document.getElementById('total-deductions');
        const netSalaryEl = document.getElementById('net-salary');

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }

        function calculateNetSalary() {
            const baseSalary = parseFloat(document.querySelector('input[name="base_salary"]').value) || 0;
            const hourlyRate = parseFloat(document.querySelector('input[name="hourly_rate"]').value) || 0;
            const overtimeRate = parseFloat(document.querySelector('input[name="overtime_rate"]').value) || 0;
            const bonusPercentage = parseFloat(document.querySelector('input[name="bonus_percentage"]').value) || 0;
            const houseRent = parseFloat(document.querySelector('input[name="house_rent"]').value) || 0;
            const conveyance = parseFloat(document.querySelector('input[name="conveyance"]').value) || 0;
            const medical = parseFloat(document.querySelector('input[name="medical"]').value) || 0;
            const performanceBonus = parseFloat(document.querySelector('input[name="performance_bonus"]').value) || 0;

            const providentFund = parseFloat(document.querySelector('input[name="provident_fund"]').value) || 0;
            const professionalTax = parseFloat(document.querySelector('input[name="professional_tax"]').value) || 0;
            const otherDeductions = parseFloat(document.querySelector('input[name="other"]').value) || 0;

            const bonusAmount = baseSalary * (bonusPercentage / 100);
            const totalEarnings = baseSalary + hourlyRate + overtimeRate + houseRent + conveyance + medical + performanceBonus + bonusAmount;
            const totalDeductions = providentFund + professionalTax + otherDeductions;
            const netSalary = totalEarnings - totalDeductions;

            totalEarningsEl.textContent = formatCurrency(totalEarnings);
            totalDeductionsEl.textContent = formatCurrency(totalDeductions);
            netSalaryEl.textContent = formatCurrency(netSalary);
        }

        inputs.forEach(input => {
            input.addEventListener('input', calculateNetSalary);
        });

        // Initial calculation
        calculateNetSalary();
    });
</script>
@endpush
