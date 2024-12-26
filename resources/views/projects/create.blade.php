@extends('layouts.app')

@section('title', 'Create New Project')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Create New Project</h2>
        
        <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Project Name *</label>
                <input type="text" name="name" id="name" required 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                       value="{{ old('name') }}">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Project Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="client_id" class="block text-sm font-medium text-gray-700">Select Client *</label>
                <div class="flex items-center space-x-2">
                    <select name="client_id" id="client_id" required 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">Select a Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('clients.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        New Client
                    </a>
                </div>
            </div>

            <div>
                <label for="hourly_rate" class="block text-sm font-medium text-gray-700">Hourly Rate *</label>
                <input type="number" name="hourly_rate" id="hourly_rate" required min="0" step="0.01"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                       value="{{ old('hourly_rate') }}">
                @error('hourly_rate')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-gray-700">Project Location</label>
                <input type="text" name="location" id="location" required 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200"
                       value="{{ old('location') }}">
                @error('location')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div id="attachments-container">
                <label class="block text-sm font-medium text-gray-700">Project Attachments</label>
                <div id="attachments-list" class="mt-2 space-y-2">
                    <div class="attachment-row flex items-center space-x-2">
                        <input type="file" name="attachments[]" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <select name="attachment_types[]" 
                                class="block w-1/3 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            @foreach($attachmentTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="removeAttachment(this)" 
                                class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addAttachment()" 
                        class="mt-2 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                    Add Attachment
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
    <label for="operator_id" class="block text-sm font-medium text-gray-700">Assign Operator</label>
    <select name="operator_id" id="operator_id" 
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
        <option value="">Select Operator</option>
        @foreach($operators as $operator)
            <option value="{{ $operator['id'] }}" {{ old('operator_id') == $operator['id'] ? 'selected' : '' }}>
                {{ $operator['name'] }}
            </option>
        @endforeach
    </select>
    @error('operator_id')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

                <div>
                    <label for="machine_id" class="block text-sm font-medium text-gray-700">Assign Machine</label>
                    <select name="machine_id" id="machine_id" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <option value="">Select Machine</option>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}" {{ old('machine_id') == $machine->id ? 'selected' : '' }}>
                                {{ $machine->name }} ({{ $machine->status }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function addAttachment() {
        const container = document.getElementById('attachments-list');
        const newRow = document.createElement('div');
        newRow.className = 'attachment-row flex items-center space-x-2 mt-2';
        newRow.innerHTML = `
            <input type="file" name="attachments[]" 
                   class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <select name="attachment_types[]" 
                    class="block w-1/3 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                @foreach($attachmentTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <button type="button" onclick="removeAttachment(this)" 
                    class="text-red-500 hover:text-red-700">
                <i class="fas fa-trash"></i>
            </button>
        `;
        container.appendChild(newRow);
    }

    function removeAttachment(button) {
        const row = button.closest('.attachment-row');
        if (document.querySelectorAll('.attachment-row').length > 1) {
            row.remove();
        }
    }
</script>
@endpush
