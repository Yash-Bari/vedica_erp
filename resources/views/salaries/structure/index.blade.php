@extends('layouts.app')

@section('title', 'Salary Structures')

@section('content')
<div class="bg-white rounded-lg shadow-md">
    <div class="border-b border-gray-200 p-4 flex justify-between items-center">
        <h3 class="text-xl font-semibold">Salary Structures</h3>
        <div>
            @can('create', App\Models\SalaryStructure::class)
            <a href="{{ route('salaries.structure.create') }}" 
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create New Salary Structure
            </a>
            @endcan
        </div>
    </div>

    <div class="p-4">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Salary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allowances</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deductions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Net Salary</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($salaryStructures as $structure)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $structure->employee->full_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ number_format($structure->base_salary, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format(
                                $structure->house_rent_allowance +
                                $structure->conveyance_allowance +
                                $structure->medical_allowance +
                                $structure->performance_bonus, 2
                            ) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ number_format(
                                $structure->provident_fund +
                                $structure->professional_tax +
                                $structure->other_deductions, 2
                            ) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ number_format($structure->calculateNetSalary(), 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $structure->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $structure->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                @can('view', $structure)
                                <a href="{{ route('salaries.structure.show', $structure->id) }}" 
                                   class="inline-flex items-center p-2 text-blue-600 hover:bg-blue-100 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @endcan

                                @can('update', $structure)
                                <a href="{{ route('salaries.structure.edit', $structure->id) }}" 
                                   class="inline-flex items-center p-2 text-yellow-600 hover:bg-yellow-100 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan

                                @can('delete', $structure)
                                <form action="{{ route('salaries.structure.destroy', $structure->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            onclick="return confirm('Are you sure you want to delete this salary structure?')"
                                            class="inline-flex items-center p-2 text-red-600 hover:bg-red-100 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center">
                            <p class="text-gray-500 mb-4">No salary structures found.</p>
                            @can('create', App\Models\SalaryStructure::class)
                            <a href="{{ route('salaries.structure.create') }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded hover:bg-blue-700">
                                Create Salary Structure
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
        {{ $salaryStructures->links() }}
    </div>
</div>
@endsection