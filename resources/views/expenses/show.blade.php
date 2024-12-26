@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
        {{-- Expense Header --}}
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">
                Expense Details
                <span class="text-sm text-gray-500 ml-2">
                    #{{ $expense->id }}
                </span>
            </h1>

            <div class="flex justify-end space-x-4 mt-6">
                <a href="{{ route('expenses.edit', $expense) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Edit Expense
                </a>
                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Are you sure you want to delete this expense?')">
                        Delete Expense
                    </button>
                </form>
                <a href="{{ route('expenses.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to List
                </a>
            </div>
        </div>

        {{-- Expense Details Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
            {{-- Left Column --}}
            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Project</label>
                    <p class="mt-1 text-lg font-semibold text-gray-900">
                        {{ $expense->project ? $expense->project->name : 'N/A' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Amount</label>
                    <p class="mt-1 text-2xl font-bold text-green-600">
                        ₹{{ number_format($expense->amount, 2) }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Expense Date</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $expense->date->format('d M Y') }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Expense Type</label>
                    @php
                        $typeClass = match($expense->type) {
                            'Material' => 'bg-blue-100 text-blue-800',
                            'Labor' => 'bg-green-100 text-green-800',
                            'Equipment' => 'bg-yellow-100 text-yellow-800',
                            'Transportation' => 'bg-purple-100 text-purple-800',
                            'Miscellaneous' => 'bg-pink-100 text-pink-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    @endphp
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $typeClass }}">
                        {{ $expense->type }}
                    </span>
                </div>
            </div>

            {{-- Right Column --}}
            <div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Expense Category</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $expense->category }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $expense->payment_method ?? 'Not Specified' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Vendor Name</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $expense->vendor_name ?? 'N/A' }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Invoice Number</label>
                    <p class="mt-1 text-lg text-gray-900">
                        {{ $expense->invoice_number ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Description Section --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <p class="text-gray-700">
                {{ $expense->description ?? 'No description provided' }}
            </p>
        </div>
    </div>
</div>
@endsection
