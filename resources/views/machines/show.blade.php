@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
        {{-- Machine Header --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">
                Machine Details
                <span class="text-sm text-gray-500 ml-2">
                    #{{ $machine->id }}
                </span>
            </h1>

            <div class="flex space-x-2">
                @can('update', $machine)
                <a href="{{ route('machines.edit', $machine) }}" 
                   class="btn btn-secondary flex items-center">
                    <x-heroicon-o-pencil class="w-5 h-5 mr-2" />
                    Edit
                </a>
                @endcan

                @can('delete', $machine)
                <form action="{{ route('machines.destroy', $machine) }}" 
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this machine?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger flex items-center">
                        <x-heroicon-o-trash class="w-5 h-5 mr-2" />
                        Delete
                    </button>
                </form>
                @endcan
            </div>
        </div>

        {{-- Machine Details Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            {{-- Left Column --}}
            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Machine Name</label>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ $machine->name }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Model Number</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->model_number ?? 'N/A' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Serial Number</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->serial_number ?? 'N/A' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Machine Type</label>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $machine->type === 'Excavator' ? 'bg-blue-100 text-blue-800' : 
                        ($machine->type === 'Bulldozer' ? 'bg-green-100 text-green-800' : 
                        ($machine->type === 'Crane' ? 'bg-yellow-100 text-yellow-800' : 
                        'bg-gray-100 text-gray-800')) }}">
                        {{ $machine->type }}
                    </span>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Machine Status</label>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $machine->status === 'Available' ? 'bg-green-100 text-green-800' : 
                        ($machine->status === 'Maintenance' ? 'bg-yellow-100 text-yellow-800' : 
                        ($machine->status === 'Repair' ? 'bg-red-100 text-red-800' : 
                        'bg-gray-100 text-gray-800')) }}">
                        {{ $machine->status }}
                    </span>
                </div>
            </div>

            {{-- Right Column --}}
            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Project</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->project ? $machine->project->name : 'Unassigned' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Purchase Price</label>
                    <p class="mt-1 text-lg font-bold text-green-600">
                        ₹{{ $machine->purchase_price ? number_format($machine->purchase_price, 2) : 'N/A' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Purchase Date</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->purchase_date ? $machine->purchase_date->format('d M Y') : 'N/A' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Manufacturer</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->manufacturer ?? 'N/A' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Year of Manufacture</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->year_of_manufacture ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Technical Specifications --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Technical Specifications</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Operating Weight</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->operating_weight ? $machine->operating_weight . ' kg' : 'N/A' }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fuel Capacity</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->fuel_capacity ? $machine->fuel_capacity . ' liters' : 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Additional Information --}}
        <div class="px-6 py-4 border-t border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Additional Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Current Location</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->current_location ?? 'Not Specified' }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Last Maintenance Date</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $machine->last_maintenance_date ? $machine->last_maintenance_date->format('d M Y') : 'No Maintenance Recorded' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Notes Section --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <p class="text-gray-700">
                {{ $machine->notes ?? 'No additional notes' }}
            </p>
        </div>
    </div>

    {{-- Related Information Sections --}}
    <div class="max-w-4xl mx-auto mt-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Maintenance History --}}
            <div class="bg-white shadow-md rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Maintenance History</h2>
                @if($machine->maintenances->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($machine->maintenances->take(3) as $maintenance)
                            <li class="py-2">
                                <div class="flex justify-between">
                                    <span>{{ $maintenance->type }}</span>
                                    <span class="text-sm text-gray-500">
                                        {{ $maintenance->date->format('d M Y') }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($machine->maintenances->count() > 3)
                        <a href="{{ route('machines.maintenances', $machine) }}" 
                           class="mt-2 text-blue-600 hover:underline text-sm">
                            View All Maintenances
                        </a>
                    @endif
                @else
                    <p class="text-gray-500">No maintenance records</p>
                @endif
            </div>

            {{-- Health Checks --}}
            <div class="bg-white shadow-md rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Health Checks</h2>
                @if($machine->healthChecks->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($machine->healthChecks->take(3) as $healthCheck)
                            <li class="py-2">
                                <div class="flex justify-between">
                                    <span>{{ $healthCheck->overall_condition }}</span>
                                    <span class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($healthCheck->check_date)->format('d M Y') }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($machine->healthChecks->count() > 3)
                        <a href="{{ route('machines.health-checks', $machine) }}" 
                           class="mt-2 text-blue-600 hover:underline text-sm">
                            View All Health Checks
                        </a>
                    @endif
                @else
                    <p class="text-gray-500">No health check records</p>
                @endif
            </div>

            {{-- Expenses --}}
            <div class="bg-white shadow-md rounded-lg p-4">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Expenses</h2>
                @if($machine->expenses->count() > 0)
                    <ul class="divide-y divide-gray-200">
                        @foreach($machine->expenses->take(3) as $expense)
                            <li class="py-2">
                                <div class="flex justify-between">
                                    <span>{{ $expense->type }}</span>
                                    <span class="text-sm text-green-600">
                                        ₹{{ number_format($expense->amount, 2) }}
                                    </span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                    @if($machine->expenses->count() > 3)
                        <a href="{{ route('machines.expenses', $machine) }}" 
                           class="mt-2 text-blue-600 hover:underline text-sm">
                            View All Expenses
                        </a>
                    @endif
                @else
                    <p class="text-gray-500">No expense records</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
