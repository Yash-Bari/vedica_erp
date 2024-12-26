@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Financial Analytics Dashboard</h1>

    {{-- Error Handling --}}
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Revenue KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Total Revenue --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600">Total Revenue (inc. GST)</h3>
            <p class="text-3xl font-bold text-green-600">
                ₹{{ number_format($kpis['total_revenue'], 2) }}
            </p>
            <div class="mt-2 text-sm">
                <p class="text-gray-600">Subtotal: ₹{{ number_format($kpis['total_subtotal'], 2) }}</p>
                <p class="text-gray-600">GST (18%): ₹{{ number_format($kpis['total_gst'], 2) }}</p>
            </div>
        </div>

        {{-- Total Expenses --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600">Total Expenses</h3>
            <p class="text-3xl font-bold text-red-600">
                ₹{{ number_format($kpis['total_expenses'], 2) }}
            </p>
        </div>

        {{-- Net Profit --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600">Net Profit (excl. GST)</h3>
            <p class="text-3xl font-bold {{ $kpis['net_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                ₹{{ number_format($kpis['net_profit'], 2) }}
            </p>
        </div>
    </div>

    {{-- Payment Status Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600">Total Invoiced</h3>
            <p class="text-2xl font-bold text-blue-600">₹{{ number_format($paymentStatus['total_invoiced'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600">Total Paid</h3>
            <p class="text-2xl font-bold text-green-600">₹{{ number_format($paymentStatus['total_paid'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600">Outstanding</h3>
            <p class="text-2xl font-bold text-red-600">₹{{ number_format($paymentStatus['total_outstanding'], 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600">Collection Ratio</h3>
            <p class="text-2xl font-bold text-purple-600">{{ number_format($paymentStatus['collection_ratio'], 1) }}%</p>
        </div>
    </div>

    {{-- GST Summary --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">GST Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-600">Total GST Collected</h4>
                <p class="text-xl font-bold text-gray-900">₹{{ number_format($gstSummary['total_gst_collected'], 2) }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-600">Monthly Average</h4>
                <p class="text-xl font-bold text-gray-900">₹{{ number_format($gstSummary['monthly_average'], 2) }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-600">Highest GST Month</h4>
                <p class="text-xl font-bold text-gray-900">{{ $gstSummary['highest_month'] }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-600">Lowest GST Month</h4>
                <p class="text-xl font-bold text-gray-900">{{ $gstSummary['lowest_month'] }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {{-- Monthly Charts --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Monthly Revenue & Net Profit</h3>
            <div class="relative" style="height: 400px;">  {{-- Fixed height container --}}
                <canvas id="monthlyFinancialChart"></canvas>
            </div>
        </div>

        {{-- Project Profitability Table --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Project Profitability</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                            <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">GST</th>
                            <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Net Profit</th>
                            <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Margin</th>
                            <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase">Outstanding</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($projectProfitability as $project)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $project['name'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">₹{{ number_format($project['revenue'], 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right">₹{{ number_format($project['gst'], 2) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right {{ $project['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₹{{ number_format($project['profit'], 2) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right {{ $project['margin'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($project['margin'], 1) }}%
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                ₹{{ number_format($project['outstanding'], 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyFinancialChart');
    
    if (!ctx) {
        console.error('Canvas element not found');
        return;
    }

    // Debug logging
    console.log('Chart Data:', {
        labels: @json($chartData['labels'] ?? []),
        revenue: @json($chartData['revenue'] ?? []),
        expenses: @json($chartData['expenses'] ?? []),
        netProfit: @json($chartData['net_profit'] ?? [])
    });

    // Create the chart
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels'] ?? []),
            datasets: [
                {
                    label: 'Revenue (inc. GST)',
                    data: @json($chartData['revenue'] ?? []),
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                    order: 1
                },
                {
                    label: 'Expenses',
                    data: @json($chartData['expenses'] ?? []),
                    backgroundColor: 'rgba(239, 68, 68, 0.6)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1,
                    order: 2
                },
                {
                    label: 'Net Profit',
                    data: @json($chartData['net_profit'] ?? []),
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    order: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = parseFloat(context.raw).toFixed(2);
                            return `${context.dataset.label}: ₹${value}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + parseFloat(value).toFixed(2);
                        }
                    }
                }
            }
        }
    });

    // Debug logging
    console.log('Chart instance created:', chart);
});
</script>
@endpush