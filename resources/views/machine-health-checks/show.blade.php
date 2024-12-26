@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">
                Machine Health Check Details
                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded 
                    @if($healthCheck->overall_condition == 'Excellent') bg-green-100 text-green-800
                    @elseif($healthCheck->overall_condition == 'Good') bg-blue-100 text-blue-800
                    @elseif($healthCheck->overall_condition == 'Fair') bg-yellow-100 text-yellow-800
                    @elseif($healthCheck->overall_condition == 'Poor') bg-orange-100 text-orange-800
                    @else bg-red-100 text-red-800 @endif">
                    {{ $healthCheck->overall_condition }}
                </span>
            </h1>
            <p class="text-sm text-gray-600">
                Checked on: {{ $healthCheck->check_date->format('d M Y') }} at {{ $healthCheck->check_time }}
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 p-6">
            <div>
                <h2 class="text-xl font-semibold mb-4">Machine Details</h2>
                <div class="space-y-3">
                    <p><strong>Machine Number:</strong> {{ $healthCheck->machine->machine_number }}</p>
                    <p><strong>Model:</strong> {{ $healthCheck->machine->model }}</p>
                    <p><strong>Type:</strong> {{ $healthCheck->machine->type }}</p>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4">System Checks</h2>
                <div class="space-y-3">
                    <p>
                        <strong>Hydraulic System:</strong> 
                        <span class="{{ $healthCheck->hydraulic_system_check ? 'text-green-600' : 'text-red-600' }}">
                            {{ $healthCheck->hydraulic_system_check ? 'Passed' : 'Failed' }}
                        </span>
                    </p>
                    <p>
                        <strong>Electrical System:</strong> 
                        <span class="{{ $healthCheck->electrical_system_check ? 'text-green-600' : 'text-red-600' }}">
                            {{ $healthCheck->electrical_system_check ? 'Passed' : 'Failed' }}
                        </span>
                    </p>
                    <p>
                        <strong>Tire Condition:</strong> 
                        <span class="{{ $healthCheck->tire_condition_check ? 'text-green-600' : 'text-red-600' }}">
                            {{ $healthCheck->tire_condition_check ? 'Passed' : 'Failed' }}
                        </span>
                    </p>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4">Technical Measurements</h2>
                <div class="space-y-3">
                    <p><strong>Engine Temperature:</strong> {{ $healthCheck->engine_temperature ?? 'N/A' }}°C</p>
                    <p><strong>Oil Pressure:</strong> {{ $healthCheck->oil_pressure ?? 'N/A' }}</p>
                    <p><strong>Fuel Level:</strong> {{ $healthCheck->fuel_level ?? 'N/A' }}%</p>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4">Maintenance Recommendation</h2>
                <div class="space-y-3">
                    <p>
                        <strong>Recommendation:</strong> 
                        <span class="
                            @if($healthCheck->maintenance_recommendation == 'Immediate Service') text-red-600
                            @elseif($healthCheck->maintenance_recommendation == 'Major Repair') text-orange-600
                            @elseif($healthCheck->maintenance_recommendation == 'Minor Repair') text-yellow-600
                            @else text-green-600 @endif
                        ">
                            {{ $healthCheck->maintenance_recommendation }}
                        </span>
                    </p>
                </div>
            </div>

            @if($healthCheck->health_check_image)
            <div class="col-span-2">
                <h2 class="text-xl font-semibold mb-4">Health Check Image</h2>
                <img src="{{ Storage::url($healthCheck->health_check_image) }}" 
                     alt="Machine Health Check" 
                     class="w-full h-auto max-h-96 object-cover rounded-lg">
            </div>
            @endif

            <div class="col-span-2">
                <h2 class="text-xl font-semibold mb-4">Remarks</h2>
                <div class="space-y-3">
                    <p><strong>Engine Remarks:</strong> {{ $healthCheck->engine_remarks ?? 'No remarks' }}</p>
                    <p><strong>Hydraulic Remarks:</strong> {{ $healthCheck->hydraulic_remarks ?? 'No remarks' }}</p>
                    <p><strong>Electrical Remarks:</strong> {{ $healthCheck->electrical_remarks ?? 'No remarks' }}</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <a href="{{ route('machines.show', $healthCheck->machine_id) }}" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                View Machine Details
            </a>
            
            @if($healthCheck->voice_note)
            <audio controls>
                <source src="{{ Storage::url($healthCheck->voice_note) }}" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            @endif
        </div>
    </div>
</div>
@endsection
