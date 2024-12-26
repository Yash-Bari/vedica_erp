@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Machine Health Check: {{ $machine->machine_number }}</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('machine-health-checks.store', $machine->id) }}" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        @csrf
        
        <div class="grid grid-cols-2 gap-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="check_date">
                    Check Date
                </label>
                <input type="date" name="check_date" id="check_date" 
                    value="{{ old('check_date') ?? now()->format('Y-m-d') }}" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="check_time">
                    Check Time
                </label>
                <input type="time" name="check_time" id="check_time" 
                    value="{{ old('check_time') ?? now()->format('H:i') }}" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="overall_condition">
                    Overall Condition
                </label>
                <select name="overall_condition" id="overall_condition" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                    required>
                    <option value="Excellent" {{ old('overall_condition') == 'Excellent' ? 'selected' : '' }}>Excellent</option>
                    <option value="Good" {{ old('overall_condition') == 'Good' || !old('overall_condition') ? 'selected' : '' }}>Good</option>
                    <option value="Fair" {{ old('overall_condition') == 'Fair' ? 'selected' : '' }}>Fair</option>
                    <option value="Poor" {{ old('overall_condition') == 'Poor' ? 'selected' : '' }}>Poor</option>
                    <option value="Critical" {{ old('overall_condition') == 'Critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="engine_temperature">
                    Engine Temperature (°C)
                </label>
                <input type="number" name="engine_temperature" id="engine_temperature" 
                    value="{{ old('engine_temperature') }}" step="0.1" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="oil_pressure">
                    Oil Pressure
                </label>
                <input type="number" name="oil_pressure" id="oil_pressure" 
                    value="{{ old('oil_pressure') }}" step="0.1" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="fuel_level">
                    Fuel Level (%)
                </label>
                <input type="number" name="fuel_level" id="fuel_level" 
                    value="{{ old('fuel_level') }}" min="0" max="100" step="0.1" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4 col-span-2">
                <div class="flex items-center">
                    <input type="checkbox" name="hydraulic_system_check" id="hydraulic_system_check" 
                        {{ old('hydraulic_system_check') ? 'checked' : '' }}
                        class="mr-2 leading-tight">
                    <label class="text-gray-700 text-sm font-bold" for="hydraulic_system_check">
                        Hydraulic System Check Passed
                    </label>
                </div>
            </div>

            <div class="mb-4 col-span-2">
                <div class="flex items-center">
                    <input type="checkbox" name="electrical_system_check" id="electrical_system_check" 
                        {{ old('electrical_system_check') ? 'checked' : '' }}
                        class="mr-2 leading-tight">
                    <label class="text-gray-700 text-sm font-bold" for="electrical_system_check">
                        Electrical System Check Passed
                    </label>
                </div>
            </div>

            <div class="mb-4 col-span-2">
                <div class="flex items-center">
                    <input type="checkbox" name="tire_condition_check" id="tire_condition_check" 
                        {{ old('tire_condition_check') ? 'checked' : '' }}
                        class="mr-2 leading-tight">
                    <label class="text-gray-700 text-sm font-bold" for="tire_condition_check">
                        Tire Condition Check Passed
                    </label>
                </div>
            </div>

            <div class="mb-4 col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="engine_remarks">
                    Engine Remarks
                </label>
                <textarea name="engine_remarks" id="engine_remarks" rows="3"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('engine_remarks') }}</textarea>
            </div>

            <div class="mb-4 col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="health_check_image">
                    Health Check Image
                </label>
                <input type="file" name="health_check_image" id="health_check_image" 
                    accept="image/*"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4 col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="voice_note">
                    Voice Note
                </label>
                <input type="file" name="voice_note" id="voice_note" 
                    accept="audio/mp3,audio/wav,audio/m4a"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
        </div>

        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Submit Health Check
            </button>
        </div>
    </form>
</div>
@endsection
