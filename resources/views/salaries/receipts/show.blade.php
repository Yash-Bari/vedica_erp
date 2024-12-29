@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg">
        <!-- Header -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Salary Receipt</h1>
                <a href="{{ route('salaries.payments.show', $payment) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Back to Payment
                </a>
            </div>
        </div>

        <!-- Receipt Content -->
        <div class="p-6">
            <!-- Receipt Header -->
            <div class="text-center mb-8">
                <h2 class="text-xl font-bold mb-2">{{ config('app.name') }}</h2>
                <p class="text-gray-600">Receipt No: {{ $receipt->receipt_number }}</p>
                <p class="text-gray-600">Generated on: {{ $receipt->generated_at->format('d M Y, h:i A') }}</p>
            </div>

            <!-- Employee Details -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Employee Details</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Name</p>
                        <p class="font-medium">{{ $employee->first_name }} {{ $employee->last_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Employee Code</p>
                        <p class="font-medium">{{ $employee->employee_code }}</p>
                    </div>
                </div>
            </div>

            <!-- Salary Period -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Salary Period</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600">Month</p>
                        <p class="font-medium">{{ DateTime::createFromFormat('!m', $payment->month)->format('F') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Year</p>
                        <p class="font-medium">{{ $payment->year }}</p>
                    </div>
                </div>
            </div>

            <!-- Earnings -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Earnings</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Basic Salary</span>
                        <span class="font-medium">₹{{ number_format($payment->basic_salary, 2) }}</span>
                    </div>
                    @foreach(json_decode($payment->allowances, true) as $type => $amount)
                        @if($amount)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                            <span class="font-medium">₹{{ number_format($amount, 2) }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Deductions -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 pb-2 border-b border-gray-200">Deductions</h3>
                <div class="space-y-3">
                    @foreach(json_decode($payment->deductions, true) as $type => $amount)
                        @if($amount)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                            <span class="font-medium text-red-600">₹{{ number_format($amount, 2) }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Net Salary -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold">Net Salary</span>
                    <span class="text-xl font-bold text-green-600">₹{{ number_format($payment->net_salary, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 text-center text-sm text-gray-600">
            This is a computer-generated receipt and does not require a signature.
        </div>
    </div>
</div>
@endsection
