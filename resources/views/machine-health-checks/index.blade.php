@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Machine Health Checks</h1>
                <p class="text-gray-600">{{ $machine->name }} - {{ $machine->model_number }}</p>
            </div>
            <a href="{{ route('machine-health-checks.create', $machine->id) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-plus mr-2"></i>
                New Health Check
            </a>
        </div>

        <!-- Health Checks List -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            @if($healthChecks->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date & Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Overall Condition
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Engine Temp
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Oil Pressure
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fuel Level
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                System Checks
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Maintenance
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($healthChecks as $check)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($check->check_date)->format('d M Y') }}<br>
                                    <span class="text-gray-500">{{ \Carbon\Carbon::parse($check->check_time)->format('h:i A') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($check->overall_condition == 'Excellent') bg-green-100 text-green-800
                                        @elseif($check->overall_condition == 'Good') bg-blue-100 text-blue-800
                                        @elseif($check->overall_condition == 'Fair') bg-yellow-100 text-yellow-800
                                        @elseif($check->overall_condition == 'Poor') bg-orange-100 text-orange-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ $check->overall_condition }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $check->engine_temperature ?? 'N/A' }} °C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $check->oil_pressure ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $check->fuel_level ?? 'N/A' }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="space-y-1">
                                        <div class="flex items-center">
                                            <i class="fas fa-circle text-xs mr-2 {{ $check->hydraulic_system_check ? 'text-green-500' : 'text-red-500' }}"></i>
                                            <span class="text-gray-600">Hydraulic</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-circle text-xs mr-2 {{ $check->electrical_system_check ? 'text-green-500' : 'text-red-500' }}"></i>
                                            <span class="text-gray-600">Electrical</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i class="fas fa-circle text-xs mr-2 {{ $check->tire_condition_check ? 'text-green-500' : 'text-red-500' }}"></i>
                                            <span class="text-gray-600">Tires</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($check->maintenance_recommendation == 'Immediate') bg-red-100 text-red-800
                                        @elseif($check->maintenance_recommendation == 'Scheduled') bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ $check->maintenance_recommendation ?? 'None' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $healthChecks->links() }}
                </div>
            @else
                <div class="p-6 text-center text-gray-500">
                    <p>No health checks recorded yet.</p>
                    <a href="{{ route('machine-health-checks.create', $machine->id) }}" 
                       class="mt-4 text-blue-500 hover:text-blue-600 font-medium">
                        Perform First Health Check
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
