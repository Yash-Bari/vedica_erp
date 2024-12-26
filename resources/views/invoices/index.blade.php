@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Invoice Management</h1>
        <a href="{{ route('invoices.create') }}" 
           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create New Invoice
        </a>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" action="{{ route('invoices.index') }}" class="flex space-x-4">
                <select name="status" class="form-select rounded-md">
                    <option value="">All Statuses</option>
                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Sent" {{ request('status') == 'Sent' ? 'selected' : '' }}>Sent</option>
                    <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                </select>

                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="form-input rounded-md">
                <input type="date" name="end_date" value="{{ request('end_date') }}" 
                       class="form-input rounded-md">

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                    Filter
                </button>
            </form>
        </div>

        <table class="w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Invoice Number
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Client
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Amount
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Invoice Date
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($invoices as $invoice)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $invoice->invoice_number }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $invoice->client->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        ₹{{ number_format($invoice->total_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="
                            px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @if($invoice->status == 'Draft') bg-yellow-100 text-yellow-800
                            @elseif($invoice->status == 'Sent') bg-blue-100 text-blue-800
                            @elseif($invoice->status == 'Paid') bg-green-100 text-green-800
                            @else bg-red-100 text-red-800 @endif
                        ">
                            {{ $invoice->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('invoices.show', $invoice) }}" 
                           class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        
                        @if($invoice->status == 'Draft')
                            <form action="{{ route('invoices.send', $invoice) }}" 
                                  method="POST" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="text-green-600 hover:text-green-900">
                                    Send
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        No invoices found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 bg-gray-50 border-t border-gray-200">
            {{ $invoices->links() }}
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('invoices.report') }}" 
           class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Generate Invoice Report
        </a>
    </div>
</div>
@endsection
