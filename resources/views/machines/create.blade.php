@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Add New Machine</h1>

        <form action="{{ route('machines.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Machine Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Machine Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" 
                        value="{{ old('name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('name') border-red-500 @enderror"
                        placeholder="Enter machine name" required />
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Model Number --}}
                <div>
                    <label for="model_number" class="block text-sm font-medium text-gray-700">
                        Model Number
                    </label>
                    <input type="text" id="model_number" name="model_number" 
                        value="{{ old('model_number') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('model_number') border-red-500 @enderror"
                        placeholder="Enter model number" />
                    @error('model_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Serial Number --}}
                <div>
                    <label for="serial_number" class="block text-sm font-medium text-gray-700">
                        Serial Number
                    </label>
                    <input type="text" id="serial_number" name="serial_number" 
                        value="{{ old('serial_number') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('serial_number') border-red-500 @enderror"
                        placeholder="Enter serial number" />
                    @error('serial_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Machine Type --}}
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">
                        Machine Type <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" 
                        class="mt-1 block w-full rounded-md border-gray-300 @error('type') border-red-500 @enderror" 
                        required>
                        <option value="">Select Machine Type</option>
                        @foreach(App\Models\Machine::TYPES as $type)
                            <option value="{{ $type }}" 
                                {{ old('type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Machine Status --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        Machine Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" 
                        class="mt-1 block w-full rounded-md border-gray-300 @error('status') border-red-500 @enderror" 
                        required>
                        <option value="">Select Status</option>
                        @foreach(App\Models\Machine::STATUS as $status)
                            <option value="{{ $status }}" 
                                {{ old('status') == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Project Assignment --}}
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700">
                        Assign to Project
                    </label>
                    <select id="project_id" name="project_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 @error('project_id') border-red-500 @enderror">
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" 
                                {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Purchase Price --}}
                <div>
                    <label for="purchase_price" class="block text-sm font-medium text-gray-700">
                        Purchase Price
                    </label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" id="purchase_price" name="purchase_price" step="0.01" min="0"
                            value="{{ old('purchase_price') }}"
                            class="pl-7 block w-full rounded-md border-gray-300 @error('purchase_price') border-red-500 @enderror"
                            placeholder="0.00" />
                    </div>
                    @error('purchase_price')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Purchase Date --}}
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700">
                        Purchase Date
                    </label>
                    <input type="date" id="purchase_date" name="purchase_date" 
                        value="{{ old('purchase_date') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('purchase_date') border-red-500 @enderror" />
                    @error('purchase_date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Manufacturer --}}
                <div>
                    <label for="manufacturer" class="block text-sm font-medium text-gray-700">
                        Manufacturer
                    </label>
                    <input type="text" id="manufacturer" name="manufacturer" 
                        value="{{ old('manufacturer') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('manufacturer') border-red-500 @enderror"
                        placeholder="Enter manufacturer name" />
                    @error('manufacturer')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Year of Manufacture --}}
                <div>
                    <label for="year_of_manufacture" class="block text-sm font-medium text-gray-700">
                        Year of Manufacture
                    </label>
                    <input type="number" id="year_of_manufacture" name="year_of_manufacture" 
                        min="1900" max="{{ now()->year }}"
                        value="{{ old('year_of_manufacture') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('year_of_manufacture') border-red-500 @enderror"
                        placeholder="Enter year of manufacture" />
                    @error('year_of_manufacture')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Operating Weight --}}
                <div>
                    <label for="operating_weight" class="block text-sm font-medium text-gray-700">
                        Operating Weight (kg)
                    </label>
                    <input type="number" id="operating_weight" name="operating_weight" step="0.01" min="0"
                        value="{{ old('operating_weight') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('operating_weight') border-red-500 @enderror"
                        placeholder="Enter operating weight" />
                    @error('operating_weight')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fuel Capacity --}}
                <div>
                    <label for="fuel_capacity" class="block text-sm font-medium text-gray-700">
                        Fuel Capacity (liters)
                    </label>
                    <input type="number" id="fuel_capacity" name="fuel_capacity" step="0.01" min="0"
                        value="{{ old('fuel_capacity') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('fuel_capacity') border-red-500 @enderror"
                        placeholder="Enter fuel capacity" />
                    @error('fuel_capacity')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                {{-- Current Location --}}
                <div>
                    <label for="current_location" class="block text-sm font-medium text-gray-700">
                        Current Location
                    </label>
                    <input type="text" id="current_location" name="current_location" 
                        value="{{ old('current_location') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('current_location') border-red-500 @enderror"
                        placeholder="Enter current machine location" />
                    @error('current_location')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">
                        Additional Notes
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('notes') border-red-500 @enderror"
                        placeholder="Any additional information about the machine">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <a href="{{ route('machines.index') }}" 
                   class="btn btn-outline">
                    Cancel
                </a>
                <button type="submit" 
                        class="btn btn-primary">
                    Add Machine
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const nameInput = document.getElementById('name');
        const typeSelect = document.getElementById('type');
        const statusSelect = document.getElementById('status');

        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Name validation
            if (!nameInput.value.trim()) {
                isValid = false;
                nameInput.classList.add('border-red-500');
            } else {
                nameInput.classList.remove('border-red-500');
            }

            // Type validation
            if (!typeSelect.value) {
                isValid = false;
                typeSelect.classList.add('border-red-500');
            } else {
                typeSelect.classList.remove('border-red-500');
            }

            // Status validation
            if (!statusSelect.value) {
                isValid = false;
                statusSelect.classList.add('border-red-500');
            } else {
                statusSelect.classList.remove('border-red-500');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
</script>
@endpush
