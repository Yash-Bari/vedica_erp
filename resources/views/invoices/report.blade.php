@extends('layouts.app')

@section('content')
<div class="container-fluid p-4 max-w-7xl mx-auto">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Invoice Report</h1>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 p-4 bg-gray-50">
            <div class="flex items-center">
                <i class="fas fa-chart-bar text-gray-500 mr-2"></i>
                <span class="font-semibold text-gray-700">Invoice Analytics</span>
            </div>
        </div>
        
        <div class="p-6">
            <!-- Date Range Filter -->
            <form action="{{ route('invoices.report') }}" method="GET" class="mb-6">
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="space-y-2">
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar text-gray-400"></i>
                            </div>
                            <input type="date" 
                                class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                id="start_date" 
                                name="start_date" 
                                value="{{ old('start_date', isset($startDate) ? (is_string($startDate) ? $startDate : $startDate->format('Y-m-d')) : now()->subMonths(3)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar text-gray-400"></i>
                            </div>
                            <input type="date" 
                                class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                id="end_date" 
                                name="end_date" 
                                value="{{ old('end_date', isset($endDate) ? (is_string($endDate) ? $endDate : $endDate->format('Y-m-d')) : now()->format('Y-m-d')) }}">
                        </div>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" 
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            Apply Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="grid md:grid-cols-3 gap-6 mb-6">
                <!-- Total Revenue Card -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg p-6 text-white shadow-lg">
                    <div class="flex flex-col">
                        <div class="text-sm opacity-75 mb-1">Total Revenue</div>
                        <div class="text-2xl font-bold mb-2">₹{{ number_format($totalRevenue ?? 0, 2) }}</div>
                    </div>
                </div>

                <!-- Total Invoices Card -->
                <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg p-6 text-white shadow-lg">
                    <div class="flex flex-col">
                        <div class="text-sm opacity-75 mb-1">Total Invoices</div>
                        <div class="text-2xl font-bold mb-2">{{ number_format($invoiceCount ?? 0) }}</div>
                    </div>
                </div>

                <!-- Average Invoice Value Card -->
                <div class="bg-gradient-to-br from-indigo-600 to-indigo-700 rounded-lg p-6 text-white shadow-lg">
                    <div class="flex flex-col">
                        <div class="text-sm opacity-75 mb-1">Average Invoice Value</div>
                        <div class="text-2xl font-bold mb-2">₹{{ number_format($averageInvoiceValue ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend Chart -->
            <!--div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="border-b border-gray-200 p-4">
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-gray-500 mr-2"></i>
                        <span class="font-semibold text-gray-700">Monthly Invoice Trend</span>
                    </div>
                </div>
                <div class="p-6">
                    <canvas id="monthlyTrendChart" width="100%" height="300"></canvas>
                </div>
            </div-->
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Monthly Revenue',
                data: [12000, 19000, 3000, 5000, 2000, 3000, 20000, 3000, 5000, 6000, 2000, 4000],
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    },
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        },
                        padding: 10
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        padding: 10
                    }
                }
            }
        }
    });
});
</script>
@endpush
@endsection