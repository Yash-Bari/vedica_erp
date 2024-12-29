@extends('layouts.app')

@section('title', 'Salary Payment Details')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            {{-- Header --}}
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-800">Salary Payment Details</h1>
                    <div class="flex space-x-3">
                        @if($payment->receipt)
                            <a href="{{ route('salaries.receipt.show', $payment->receipt) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                <i class="fas fa-eye mr-2"></i>View Receipt
                            </a>
                        @endif
                        <a href="{{ route('salaries.payments.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Back to Payments
                        </a>
                    </div>
                </div>
            </div>

            {{-- Employee Information --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Employee Information</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Employee Name</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $payment->employee->full_name }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Employee Code</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $payment->employee->employee_code }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Department</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $payment->employee->department }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Designation</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $payment->employee->designation }}</p>
                    </div>
                </div>
            </div>

            {{-- Payment Details --}}
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Payment Details</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Payment Period</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $payment->month }} {{ $payment->year }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Payment Status</h3>
                        <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $payment->status === 'Paid' ? 'bg-green-100 text-green-800' : 
                               ($payment->status === 'Processing' ? 'bg-yellow-100 text-yellow-800' : 
                               ($payment->status === 'Failed' ? 'bg-red-100 text-red-800' : 
                               'bg-gray-100 text-gray-800')) }}">
                            {{ $payment->status }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Payment Method</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $payment->payment_method }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Payment Date</h3>
                        <p class="mt-1 text-lg text-gray-900">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Salary Breakdown --}}
            <div class="px-6 py-4">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Salary Breakdown</h2>
                
                {{-- Earnings --}}
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-3">Earnings</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Base Salary</span>
                                <span class="font-medium">₹{{ number_format($payment->base_salary, 2) }}</span>
                            </div>
                            @if($payment->overtime_pay > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Overtime Pay ({{ $payment->overtime_hours }} hours @ ₹{{ number_format($payment->overtime_rate, 2) }}/hr)</span>
                                    <span class="font-medium">₹{{ number_format($payment->overtime_pay, 2) }}</span>
                                </div>
                            @endif
                            @if($payment->bonus > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Bonus</span>
                                    <span class="font-medium">₹{{ number_format($payment->bonus, 2) }}</span>
                                </div>
                            @endif
                            @if($payment->additional_allowances > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Additional Allowances</span>
                                    <span class="font-medium">₹{{ number_format($payment->additional_allowances, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t">
                                <span class="font-medium">Total Earnings</span>
                                <span class="font-medium text-green-600">₹{{ number_format($payment->total_earnings, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deductions --}}
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-3">Deductions</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-3">
                            @if($payment->tax_deduction > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax Deduction</span>
                                    <span class="font-medium">₹{{ number_format($payment->tax_deduction, 2) }}</span>
                                </div>
                            @endif
                            @if($payment->other_deductions > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Other Deductions</span>
                                    <span class="font-medium">₹{{ number_format($payment->other_deductions, 2) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t">
                                <span class="font-medium">Total Deductions</span>
                                <span class="font-medium text-red-600">₹{{ number_format($payment->total_deductions, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Net Salary --}}
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-900">Net Salary</span>
                        <span class="text-2xl font-bold text-blue-600">₹{{ number_format($payment->net_salary, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Receipt Information --}}
            @if($payment->receipt)
                <div class="px-6 py-4 border-t border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Receipt Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Receipt Number</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $payment->receipt->receipt_number }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Generated On</h3>
                            <p class="mt-1 text-lg text-gray-900">{{ $payment->receipt->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
