@extends('layouts.app')

@section('title', 'Salary Structure Details')

@section('content')
<div class="container mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-700">Salary Structure Details</h1>
            <div class="space-x-2">
                @can('update', $salaryStructure)
                <a href="{{ route('salaries.structure.edit', $salaryStructure->id) }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endcan
                @can('delete', $salaryStructure)
                <form action="{{ route('salaries.structure.destroy', $salaryStructure->id) }}" 
                      method="POST" class="inline" 
                      onsubmit="return confirm('Are you sure you want to delete this salary structure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <h2 class="text-lg font-semibold mb-2">Employee Information</h2>
                <div class="bg-gray-100 p-4 rounded-md">
                    <p><strong>Name:</strong> {{ $salaryStructure->employee->full_name }}</p>
                    <p><strong>Employee Role:</strong> {{ $salaryStructure->employee->role }}</p>
                    
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">Salary Structure</h2>
                <div class="bg-gray-100 p-4 rounded-md">
                    <p><strong>Base Salary:</strong> {{ number_format($salaryStructure->base_salary, 2) }}</p>
                    <p><strong>Status:</strong> 
                        <span class="{{ $salaryStructure->is_active ? 'text-green-600' : 'text-red-600' }}">
                            {{ $salaryStructure->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">Earnings</h2>
            <table class="w-full border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-200 px-4 py-2">Allowance</th>
                        <th class="border border-gray-200 px-4 py-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">House Rent Allowance</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($salaryStructure->house_rent_allowance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Conveyance Allowance</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($salaryStructure->conveyance_allowance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Medical Allowance</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($salaryStructure->medical_allowance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Performance Bonus</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($salaryStructure->performance_bonus, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">Deductions</h2>
            <table class="w-full border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-200 px-4 py-2">Deduction</th>
                        <th class="border border-gray-200 px-4 py-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Provident Fund</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($salaryStructure->provident_fund, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Professional Tax</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($salaryStructure->professional_tax, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Other Deductions</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($salaryStructure->other_deductions, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6 bg-blue-100 p-4 rounded-md">
            <h2 class="text-lg font-semibold mb-2">Net Salary Calculation</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p><strong>Total Earnings:</strong> 
                        {{ number_format(
                            $salaryStructure->base_salary + 
                            $salaryStructure->house_rent_allowance + 
                            $salaryStructure->conveyance_allowance + 
                            $salaryStructure->medical_allowance + 
                            $salaryStructure->performance_bonus, 
                            2
                        ) }}
                    </p>
                </div>
                <div>
                    <p><strong>Total Deductions:</strong> 
                        {{ number_format(
                            $salaryStructure->provident_fund + 
                            $salaryStructure->professional_tax + 
                            $salaryStructure->other_deductions, 
                            2
                        ) }}
                    </p>
                </div>
                <div class="col-span-2">
                    <p class="text-xl font-bold">
                        <strong>Net Salary:</strong> 
                        {{ number_format($salaryStructure->calculateNetSalary(), 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
