@extends('layouts.app')

@section('title', 'Salary Structure Details')

@section('content')
<div class="container mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-700">Salary Structure Details</h1>
            <div class="space-x-2">
                @can('update', $structure)
                <a href="{{ route('salaries.structure.edit', $structure->id) }}" 
                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endcan
                @can('delete', $structure)
                <form action="{{ route('salaries.structure.destroy', $structure->id) }}" 
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
                    <p><strong>Name:</strong> {{ $employee->full_name }}</p>
                    <p><strong>Employee Role:</strong> {{ $employee->role }}</p>
                    <p><strong>Employee Code:</strong> {{ $employee->employee_code }}</p>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-semibold mb-2">Base Pay Information</h2>
                <div class="bg-gray-100 p-4 rounded-md">
                    <p><strong>Base Salary:</strong> {{ number_format($structure->base_salary, 2) }}</p>
                    <p><strong>Hourly Rate:</strong> {{ number_format($structure->hourly_rate, 2) }}</p>
                    <p><strong>Overtime Rate:</strong> {{ number_format($structure->overtime_rate, 2) }}</p>
                    <p><strong>Bonus Percentage:</strong> {{ number_format($structure->bonus_percentage, 2) }}%</p>
                    <p><strong>Status:</strong> 
                        <span class="{{ $structure->is_active ? 'text-green-600' : 'text-red-600' }}">
                            {{ $structure->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                    <p><strong>Effective Date:</strong> {{ $structure->effective_date->format('d M Y') }}</p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">Earnings</h2>
            <table class="w-full border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-200 px-4 py-2">Component</th>
                        <th class="border border-gray-200 px-4 py-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Base Salary</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($structure->base_salary, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">House Rent Allowance</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($allowances['house_rent'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Conveyance Allowance</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($allowances['conveyance'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Medical Allowance</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($allowances['medical'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Performance Bonus</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($allowances['performance_bonus'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="bg-blue-50">
                        <td class="border border-gray-200 px-4 py-2 font-semibold">Total Earnings</td>
                        <td class="border border-gray-200 px-4 py-2 font-semibold">{{ number_format($structure->base_salary + $totalAllowances, 2) }}</td>
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
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($deductions['provident_fund'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Professional Tax</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($deductions['professional_tax'] ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="border border-gray-200 px-4 py-2">Other Deductions</td>
                        <td class="border border-gray-200 px-4 py-2">{{ number_format($deductions['other'] ?? 0, 2) }}</td>
                    </tr>
                    <tr class="bg-red-50">
                        <td class="border border-gray-200 px-4 py-2 font-semibold">Total Deductions</td>
                        <td class="border border-gray-200 px-4 py-2 font-semibold">{{ number_format($totalDeductions, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <div class="bg-green-50 p-4 rounded-md">
                <h2 class="text-lg font-semibold text-green-700">Net Salary</h2>
                <p class="text-2xl font-bold text-green-800 mt-2">{{ number_format($netSalary, 2) }}</p>
                <p class="text-sm text-green-600 mt-1">After all earnings and deductions</p>
            </div>
        </div>
    </div>
</div>
@endsection
