@extends('layouts.app')

@section('title', 'Edit Salary Structure')

@section('content')
<div class="container mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-700 mb-4">Edit Salary Structure</h1>
        
        @can('update', $salaryStructure)
        <form action="{{ route('salaries.structure.update', $salaryStructure->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Employee</label>
                    <select name="employee_id" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" 
                                {{ $salaryStructure->employee_id == $employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Base Salary</label>
                    <input type="number" name="base_salary" 
                           value="{{ $salaryStructure->base_salary }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           required min="0" step="0.01">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">House Rent Allowance</label>
                    <input type="number" name="house_rent_allowance" 
                           value="{{ $salaryStructure->house_rent_allowance }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Conveyance Allowance</label>
                    <input type="number" name="conveyance_allowance" 
                           value="{{ $salaryStructure->conveyance_allowance }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Medical Allowance</label>
                    <input type="number" name="medical_allowance" 
                           value="{{ $salaryStructure->medical_allowance }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Performance Bonus</label>
                    <input type="number" name="performance_bonus" 
                           value="{{ $salaryStructure->performance_bonus }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Provident Fund</label>
                    <input type="number" name="provident_fund" 
                           value="{{ $salaryStructure->provident_fund }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Professional Tax</label>
                    <input type="number" name="professional_tax" 
                           value="{{ $salaryStructure->professional_tax }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Other Deductions</label>
                    <input type="number" name="other_deductions" 
                           value="{{ $salaryStructure->other_deductions }}"
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                           min="0" step="0.01">
                </div>
            </div>

            <div class="mt-6 bg-blue-100 p-4 rounded-md">
                <h2 class="text-lg font-semibold mb-2">Net Salary Preview</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p><strong>Total Earnings:</strong> 
                            <span id="total-earnings">0.00</span>
                        </p>
                    </div>
                    <div>
                        <p><strong>Total Deductions:</strong> 
                            <span id="total-deductions">0.00</span>
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xl font-bold">
                            <strong>Net Salary:</strong> 
                            <span id="net-salary">0.00</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Update Salary Structure
                    </button>
                    <a href="{{ route('salaries.structure.index') }}" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                        Cancel
                    </a>
                </div>
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

            totalEarningsEl.textContent = totalEarnings.toFixed(2);
            totalDeductionsEl.textContent = totalDeductions.toFixed(2);
            netSalaryEl.textContent = netSalary.toFixed(2);
        }

        inputs.forEach(input => {
            input.addEventListener('input', calculateNetSalary);
        });

        // Initial calculation
        calculateNetSalary();
    });
</script>
@endpush
