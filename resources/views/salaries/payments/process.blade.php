@extends('layouts.app')

@section('title', 'Process Salary Payment')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Process Salary Payment</h1>
                    <a href="{{ route('salaries.index') }}" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>

            {{-- Employee Information --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Employee Name</h3>
                        <p class="mt-1 text-lg font-medium text-gray-900">{{ $employee->full_name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Employee Code</h3>
                        <p class="mt-1 text-lg font-medium text-gray-900">{{ $employee->employee_code }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Department</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $employee->department }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Designation</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $employee->designation }}</p>
                    </div>
                </div>
            </div>

            {{-- Payment Details Form --}}
            <form action="{{ route('salaries.payments.store', $employee) }}" method="POST" class="p-6">
                @csrf

                {{-- Payment Period --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Period</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                            <select name="month" id="month" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
                                    <option value="{{ $month }}" {{ date('F') == $month ? 'selected' : '' }}>
                                        {{ $month }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700">Year</label>
                            <select name="year" id="year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @for($y = date('Y'); $y >= date('Y')-2; $y--)
                                    <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Salary Structure Details --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Salary Structure</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Base Salary</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">
                                    ₹{{ number_format($employee->activeSalaryStructure->base_salary, 2) }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Hourly Rate</label>
                                <p class="mt-1 text-lg text-gray-900">
                                    ₹{{ number_format($employee->activeSalaryStructure->hourly_rate, 2) }}/hour
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Attendance & Overtime --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance & Overtime</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="days_worked" class="block text-sm font-medium text-gray-700">Days Worked</label>
                            <input type="number" name="days_worked" id="days_worked" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   min="0" max="31" value="{{ old('days_worked', 22) }}"
                                   onchange="calculateSalary()">
                        </div>
                        <div>
                            <label for="overtime_hours" class="block text-sm font-medium text-gray-700">Overtime Hours</label>
                            <input type="number" name="overtime_hours" id="overtime_hours" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   min="0" value="{{ old('overtime_hours', 0) }}"
                                   onchange="calculateSalary()">
                        </div>
                    </div>
                </div>

                {{-- Additional Earnings --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Earnings</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="bonus" class="block text-sm font-medium text-gray-700">Bonus</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" name="bonus" id="bonus" 
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       min="0" value="{{ old('bonus', 0) }}"
                                       onchange="calculateSalary()">
                            </div>
                        </div>
                        <div>
                            <label for="allowances" class="block text-sm font-medium text-gray-700">Additional Allowances</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" name="allowances" id="allowances" 
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       min="0" value="{{ old('allowances', 0) }}"
                                       onchange="calculateSalary()">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deductions --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Deductions</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="tax_deduction" class="block text-sm font-medium text-gray-700">Tax Deduction</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" name="tax_deduction" id="tax_deduction" 
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       min="0" value="{{ old('tax_deduction', 0) }}"
                                       onchange="calculateSalary()">
                            </div>
                        </div>
                        <div>
                            <label for="other_deductions" class="block text-sm font-medium text-gray-700">Other Deductions</label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">₹</span>
                                </div>
                                <input type="number" name="other_deductions" id="other_deductions" 
                                       class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       min="0" value="{{ old('other_deductions', 0) }}"
                                       onchange="calculateSalary()">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Method</h3>
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Select Payment Method</label>
                        <select name="payment_method" id="payment_method" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Summary</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Base Pay</span>
                                <span class="font-medium" id="summary_base_pay">₹0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Overtime Pay</span>
                                <span class="font-medium" id="summary_overtime_pay">₹0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Bonus</span>
                                <span class="font-medium" id="summary_bonus">₹0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Additional Allowances</span>
                                <span class="font-medium" id="summary_allowances">₹0.00</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t">
                                <span class="font-medium">Total Earnings</span>
                                <span class="font-medium text-green-600" id="summary_total_earnings">₹0.00</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t">
                                <span class="font-medium">Total Deductions</span>
                                <span class="font-medium text-red-600" id="summary_total_deductions">₹0.00</span>
                            </div>
                            <div class="flex justify-between pt-2 border-t">
                                <span class="font-bold">Net Salary</span>
                                <span class="font-bold text-blue-600" id="summary_net_salary">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('salaries.index') }}" 
                       class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toFixed(2);
}

function calculateSalary() {
    // Get form values
    const daysWorked = parseFloat(document.getElementById('days_worked').value) || 0;
    const overtimeHours = parseFloat(document.getElementById('overtime_hours').value) || 0;
    const bonus = parseFloat(document.getElementById('bonus').value) || 0;
    const allowances = parseFloat(document.getElementById('allowances').value) || 0;
    const taxDeduction = parseFloat(document.getElementById('tax_deduction').value) || 0;
    const otherDeductions = parseFloat(document.getElementById('other_deductions').value) || 0;

    // Get salary structure values
    const baseSalary = {{ $employee->activeSalaryStructure->base_salary }};
    const hourlyRate = {{ $employee->activeSalaryStructure->hourly_rate }};
    const workingDays = 22; // Standard working days

    // Calculate components
    const dailyRate = baseSalary / workingDays;
    const basePay = dailyRate * daysWorked;
    const overtimePay = overtimeHours * hourlyRate * 1.5; // 1.5x for overtime
    const totalEarnings = basePay + overtimePay + bonus + allowances;
    const totalDeductions = taxDeduction + otherDeductions;
    const netSalary = totalEarnings - totalDeductions;

    // Update summary
    document.getElementById('summary_base_pay').textContent = formatCurrency(basePay);
    document.getElementById('summary_overtime_pay').textContent = formatCurrency(overtimePay);
    document.getElementById('summary_bonus').textContent = formatCurrency(bonus);
    document.getElementById('summary_allowances').textContent = formatCurrency(allowances);
    document.getElementById('summary_total_earnings').textContent = formatCurrency(totalEarnings);
    document.getElementById('summary_total_deductions').textContent = formatCurrency(totalDeductions);
    document.getElementById('summary_net_salary').textContent = formatCurrency(netSalary);
}

// Calculate initial values
document.addEventListener('DOMContentLoaded', function() {
    calculateSalary();
});
</script>
@endpush
@endsection
