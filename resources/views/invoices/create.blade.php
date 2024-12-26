@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6">Create New Invoice</h1>

        @if($projects->isEmpty())
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            No completed projects available for invoicing. Projects must be marked as completed and have revenue to be eligible for invoicing.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
            @csrf

            <!-- Project Selection -->
            <div class="mb-4">
                <label for="project_id" class="block text-sm font-medium text-gray-700">Select Project</label>
                <select name="project_id" id="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Select a Project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" data-revenue="{{ $project->total_revenue }}">
                            {{ $project->name }} - Revenue: ₹{{ number_format($project->total_revenue, 2) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Invoice Details -->
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="invoice_date" class="block text-sm font-medium text-gray-700">Invoice Date</label>
                    <input type="date" name="invoice_date" id="invoice_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>

                <div class="mb-4">
                    <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" name="due_date" id="due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                </div>
            </div>

            <!-- Amount Details -->
            <div class="bg-gray-50 p-4 rounded-md mb-4">
                <h3 class="text-lg font-medium mb-4">Amount Details</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="subtotal" class="block text-sm font-medium text-gray-700">Subtotal</label>
                        <input type="number" name="subtotal" id="subtotal" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" readonly>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">GST (18%)</label>
                        <input type="text" id="gst_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50" readonly>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                        <input type="text" id="total_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-50" readonly>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('invoices.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Cancel
                </a>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Create Invoice
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('project_id').addEventListener('change', function() {
    const projectId = this.value;
    if (!projectId) {
        document.getElementById('subtotal').value = '';
        document.getElementById('gst_amount').value = '';
        document.getElementById('total_amount').value = '';
        return;
    }

    const selectedOption = this.options[this.selectedIndex];
    const revenue = parseFloat(selectedOption.dataset.revenue);
    const gstRate = 0.18; // 18%
    
    const subtotal = revenue;
    const gstAmount = subtotal * gstRate;
    const totalAmount = subtotal + gstAmount;

    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('gst_amount').value = gstAmount.toFixed(2);
    document.getElementById('total_amount').value = totalAmount.toFixed(2);
});

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const thirtyDaysFromNow = new Date(today);
    thirtyDaysFromNow.setDate(today.getDate() + 30);

    document.getElementById('invoice_date').value = today.toISOString().split('T')[0];
    document.getElementById('due_date').value = thirtyDaysFromNow.toISOString().split('T')[0];
});
</script>
@endpush
@endsection
