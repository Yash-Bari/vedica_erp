@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-5xl">
    <div class="bg-gradient-to-r from-white to-gray-50 rounded-xl shadow-lg overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-bold tracking-tight">Salary Receipt</h3>
                    <p class="text-blue-100 mt-1">Period: {{ \Carbon\Carbon::parse($salaryReceipt->payment_date)->format('F Y') }}</p>
                </div>
                <button id="print-receipt" class="bg-white text-blue-600 px-6 py-2 rounded-lg hover:bg-blue-50 transition-colors duration-200 flex items-center gap-2 font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Receipt
                </button>
            </div>
        </div>

        <div class="p-8" id="receipt-content">
            <!-- Employee and Payment Info Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Employee Details
                    </h4>
                    <div class="space-y-3 text-gray-600">
                        <p class="flex justify-between">
                            <span class="text-gray-500">Full Name</span>
                            <span class="font-medium">{{ $salaryReceipt->employee->full_name }}</span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-gray-500">Role</span>
                            <span class="font-medium">{{ $salaryReceipt->employee->role }}</span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-gray-500">Contact</span>
                            <span class="font-medium">{{ $salaryReceipt->employee->phone_number }}</span>
                        </p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Payment Details
                    </h4>
                    <div class="space-y-3 text-gray-600">
                        <p class="flex justify-between">
                            <span class="text-gray-500">Receipt Number</span>
                            <span class="font-medium">{{ $salaryReceipt->receipt_number }}</span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-gray-500">Payment Date</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($salaryReceipt->payment_date)->format('d M Y') }}</span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-gray-500">Payment Method</span>
                            <span class="font-medium">{{ $salaryReceipt->payment_method }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Salary Breakdown Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Earnings Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-green-50 p-4 border-b border-gray-100">
                        <h4 class="text-lg font-semibold text-green-700 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Earnings
                        </h4>
                    </div>
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-gray-600">Base Salary</td>
                                <td class="p-4 text-right font-medium">{{ number_format($salaryReceipt->salaryPayment->basic_salary, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-gray-600">House Rent Allowance</td>
                                <td class="p-4 text-right font-medium">{{ number_format($salaryReceipt->salaryPayment->house_rent_allowance ?? 0, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-gray-600">Conveyance Allowance</td>
                                <td class="p-4 text-right font-medium">{{ number_format($salaryReceipt->salaryPayment->conveyance_allowance ?? 0, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-gray-600">Medical Allowance</td>
                                <td class="p-4 text-right font-medium">{{ number_format($salaryReceipt->salaryPayment->medical_allowance ?? 0, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-gray-600">Performance Bonus</td>
                                <td class="p-4 text-right font-medium">{{ number_format($salaryReceipt->salaryPayment->performance_bonus ?? 0, 2) }}</td>
                            </tr>
                            <tr class="bg-green-50">
                                <td class="p-4 font-semibold text-green-700">Total Earnings</td>
                                <td class="p-4 text-right font-semibold text-green-700">{{ number_format($salaryReceipt->total_earnings, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Deductions Table -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-red-50 p-4 border-b border-gray-100">
                        <h4 class="text-lg font-semibold text-red-700 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            Deductions
                        </h4>
                    </div>
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-gray-600">Provident Fund</td>
                                <td class="p-4 text-right font-medium">{{ number_format($salaryReceipt->salaryPayment->provident_fund ?? 0, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-gray-600">Professional Tax</td>
                                <td class="p-4 text-right font-medium">{{ number_format($salaryReceipt->salaryPayment->professional_tax ?? 0, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="p-4 text-gray-600">Other Deductions</td>
                                <td class="p-4 text-right font-medium">{{ number_format($salaryReceipt->salaryPayment->other_deductions ?? 0, 2) }}</td>
                            </tr>
                            <tr class="bg-red-50">
                                <td class="p-4 font-semibold text-red-700">Total Deductions</td>
                                <td class="p-4 text-right font-semibold text-red-700">{{ number_format($salaryReceipt->total_deductions, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Net Salary Section -->
            <div class="mt-8 bg-blue-50 rounded-lg p-6 border border-blue-100">
                <div class="flex justify-between items-center">
                    <h4 class="text-xl font-bold text-blue-700">Net Salary</h4>
                    <span class="text-2xl font-bold text-blue-700">{{ number_format($salaryReceipt->net_salary, 2) }}</span>
                </div>
            </div>

            <!-- Remarks Section -->
            <div class="mt-8 bg-gray-50 rounded-lg p-6 border border-gray-100">
                <h4 class="text-lg font-semibold text-gray-700 mb-2">Remarks</h4>
                <p class="text-gray-600">{{ $salaryReceipt->remarks ?? 'No additional remarks' }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center p-6 bg-gray-50 border-t border-gray-100">
            <p class="text-gray-500 text-sm">This is a computer-generated receipt. No signature required.</p>
            <p class="text-gray-400 text-xs mt-1">Generated on {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        #receipt-content, #receipt-content * {
            visibility: visible;
        }
        #receipt-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .card-header, .card-footer {
            display: none !important;
        }
        @page {
            size: A4;
            margin: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const printButton = document.getElementById('print-receipt');
        if (printButton) {
            printButton.addEventListener('click', function() {
                window.print();
            });
        }
    });
</script>
@endpush