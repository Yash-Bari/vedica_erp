@extends('layouts.app')

@section('title', 'Process Salary Payment')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Process Salary Payment</h1>
        </div>

        <form action="{{ route('salaries.store-payment', ['employee' => $salaryPayment->employee->id]) }}" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Employee Details</h2>
                    <div class="space-y-2">
                        <p><strong>Name:</strong> {{ $salaryPayment->employee->full_name }}</p>
                        <p><strong>Employee Role:</strong> {{ $salaryPayment->employee->role }}</p>
                    </div>
                </div>

                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Salary Details</h2>
                    <div class="space-y-2">
                        <p><strong>Base Salary:</strong> {{ number_format($salaryPayment->employee->activeSalaryStructure->base_salary, 2) }}</p>
                        <p><strong>House Rent Allowance:</strong> {{ number_format($salaryPayment->employee->activeSalaryStructure->house_rent_allowance ?? 0, 2) }}</p>
                        <p><strong>Conveyance Allowance:</strong> {{ number_format($salaryPayment->employee->activeSalaryStructure->conveyance_allowance ?? 0, 2) }}</p>
                        <p><strong>Medical Allowance:</strong> {{ number_format($salaryPayment->employee->activeSalaryStructure->medical_allowance ?? 0, 2) }}</p>
                        <p><strong>Performance Bonus:</strong> {{ number_format($salaryPayment->employee->activeSalaryStructure->performance_bonus ?? 0, 2) }}</p>
                        
                        <hr class="my-2 border-gray-200">
                        
                        <p>
                            <strong>Total Earnings:</strong> 
                            <span class="text-green-600 font-bold">
                                {{ number_format(
                                    $salaryPayment->employee->activeSalaryStructure->base_salary +
                                    ($salaryPayment->employee->activeSalaryStructure->house_rent_allowance ?? 0) +
                                    ($salaryPayment->employee->activeSalaryStructure->conveyance_allowance ?? 0) +
                                    ($salaryPayment->employee->activeSalaryStructure->medical_allowance ?? 0) +
                                    ($salaryPayment->employee->activeSalaryStructure->performance_bonus ?? 0), 
                                2) }}
                            </span>
                        </p>
                        
                        <p>
                            <strong>Total Deductions:</strong> 
                            <span class="text-red-600 font-bold">
                                {{ number_format(
                                    ($salaryPayment->employee->activeSalaryStructure->provident_fund ?? 0) +
                                    ($salaryPayment->employee->activeSalaryStructure->professional_tax ?? 0) +
                                    ($salaryPayment->employee->activeSalaryStructure->other_deductions ?? 0), 
                                2) }}
                            </span>
                        </p>
                        
                        <p>
                            <strong>Net Salary:</strong> 
                            <span class="text-blue-600 font-bold text-lg">
                                {{ number_format(
                                    $salaryPayment->employee->activeSalaryStructure->base_salary +
                                    ($salaryPayment->employee->activeSalaryStructure->house_rent_allowance ?? 0) +
                                    ($salaryPayment->employee->activeSalaryStructure->conveyance_allowance ?? 0) +
                                    ($salaryPayment->employee->activeSalaryStructure->medical_allowance ?? 0) +
                                    ($salaryPayment->employee->activeSalaryStructure->performance_bonus ?? 0) -
                                    ($salaryPayment->employee->activeSalaryStructure->provident_fund ?? 0) -
                                    ($salaryPayment->employee->activeSalaryStructure->professional_tax ?? 0) -
                                    ($salaryPayment->employee->activeSalaryStructure->other_deductions ?? 0), 
                                2) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-lg p-6 mt-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Payment Processing</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">Select Payment Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700">Payment Date</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ now()->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                </div>

                <div class="mt-4">
                    <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks (Optional)</label>
                    <textarea name="remarks" id="remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Process Salary Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
