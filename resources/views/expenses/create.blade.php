@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Create New Expense</h1>

        <form action="{{ route('expenses.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Project Selection --}}
                <div>
                    <label for="project_id" class="block text-sm font-medium text-gray-700">
                        Project <span class="text-red-500">*</span>
                    </label>
                    <select id="project_id" name="project_id" 
                        class="mt-1 block w-full rounded-md border-gray-300 @error('project_id') border-red-500 @enderror">
                        <option value="">Select a Project</option>
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

                {{-- Expense Amount --}}
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">
                        Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="relative mt-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">₹</span>
                        </div>
                        <input type="number" id="amount" name="amount" step="0.01" min="0"
                            value="{{ old('amount') }}"
                            class="pl-7 block w-full rounded-md border-gray-300 @error('amount') border-red-500 @enderror"
                            placeholder="0.00" />
                    </div>
                    @error('amount')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Expense Date --}}
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">
                        Expense Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date" name="date" 
                        value="{{ old('date', now()->format('Y-m-d')) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('date') border-red-500 @enderror" />
                    @error('date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Expense Type --}}
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">
                        Expense Type <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type" 
                        class="mt-1 block w-full rounded-md border-gray-300 @error('type') border-red-500 @enderror">
                        <option value="">Select Expense Type</option>
                        @foreach(['Material', 'Labor', 'Equipment', 'Transportation', 'Miscellaneous'] as $type)
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
                {{-- Expense Category --}}
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">
                        Expense Category <span class="text-red-500">*</span>
                    </label>
                    <select id="category" name="category" 
                        class="mt-1 block w-full rounded-md border-gray-300 @error('category') border-red-500 @enderror">
                        <option value="">Select Category</option>
                        <option value="Direct" {{ old('category') == 'Direct' ? 'selected' : '' }}>Direct</option>
                        <option value="Indirect" {{ old('category') == 'Indirect' ? 'selected' : '' }}>Indirect</option>
                    </select>
                    @error('category')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Payment Method --}}
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">
                        Payment Method
                    </label>
                    <select id="payment_method" name="payment_method" 
                        class="mt-1 block w-full rounded-md border-gray-300 @error('payment_method') border-red-500 @enderror">
                        <option value="">Select Payment Method</option>
                        @foreach(['Cash', 'Bank Transfer', 'Credit Card', 'Debit Card', 'Cheque'] as $method)
                            <option value="{{ $method }}" 
                                {{ old('payment_method') == $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6">
                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <textarea id="description" name="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('description') border-red-500 @enderror"
                        placeholder="Add any additional details about the expense">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Vendor Name --}}
                <div>
                    <label for="vendor_name" class="block text-sm font-medium text-gray-700">
                        Vendor Name
                    </label>
                    <input type="text" id="vendor_name" name="vendor_name" 
                        value="{{ old('vendor_name') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('vendor_name') border-red-500 @enderror"
                        placeholder="Enter vendor name" />
                    @error('vendor_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Invoice Number --}}
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-gray-700">
                        Invoice Number
                    </label>
                    <input type="text" id="invoice_number" name="invoice_number" 
                        value="{{ old('invoice_number') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 @error('invoice_number') border-red-500 @enderror"
                        placeholder="Enter invoice number" />
                    @error('invoice_number')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end space-x-4 mt-6">
                <a href="{{ route('expenses.index') }}" 
                   class="btn btn-outline">
                    Cancel
                </a>
                <button type="submit" 
                        class="btn btn-primary">
                    Create Expense
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
        const amountInput = document.getElementById('amount');
        const dateInput = document.getElementById('date');

        // Client-side validation
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Amount validation
            if (amountInput.value <= 0) {
                isValid = false;
                amountInput.classList.add('border-red-500');
            } else {
                amountInput.classList.remove('border-red-500');
            }

            // Date validation
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            if (selectedDate > today) {
                isValid = false;
                dateInput.classList.add('border-red-500');
            } else {
                dateInput.classList.remove('border-red-500');
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush
