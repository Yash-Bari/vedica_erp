@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Financial Forecast</h1>

        {{-- Historical Revenue Chart --}}
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Historical Revenue Trend</h2>
            <canvas id="historicalRevenueChart" class="w-full h-64"></canvas>
        </div>

        {{-- Forecast Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-blue-800">Average Growth Rate</h3>
                <p class="text-2xl font-bold text-blue-900">
                    {{ number_format($averageGrowthRate, 2) }}%
                </p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-green-800">Last Month Revenue</h3>
                <p class="text-2xl font-bold text-green-900">
                    ₹{{ number_format($historicalRevenue->last()->revenue, 2) }}
                </p>
            </div>
            <div class="bg-purple-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-purple-800">Projected 6-Month Revenue</h3>
                <p class="text-2xl font-bold text-purple-900">
                    ₹{{ number_format($forecast->last()['projected_revenue'], 2) }}
                </p>
            </div>
        </div>

        {{-- Forecast Chart --}}
        <div>
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Revenue Forecast (Next 6 Months)</h2>
            <canvas id="forecastChart" class="w-full h-64"></canvas>
        </div>

        {{-- Forecast Table --}}
        <div class="mt-8">
            <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Month</th>
                        <th class="px-4 py-2 text-right">Projected Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($forecast as $projection)
                    <tr class="border-b">
                        <td class="px-4 py-2">
                            {{ $projection['month']->format('F Y') }}
                        </td>
                        <td class="px-4 py-2 text-right text-green-600">
                            ₹{{ number_format($projection['projected_revenue'], 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Historical Revenue Chart
    const historicalCtx = document.getElementById('historicalRevenueChart').getContext('2d');
    new Chart(historicalCtx, {
        type: 'line',
        data: {
            labels: @json($historicalRevenue->map(fn($data) => $data->year . '-' . str_pad($data->month, 2, '0', STR_PAD_LEFT))),
            datasets: [{
                label: 'Historical Revenue',
                data: @json($historicalRevenue->pluck('revenue')),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Forecast Chart
    const forecastCtx = document.getElementById('forecastChart').getContext('2d');
    new Chart(forecastCtx, {
        type: 'line',
        data: {
            labels: @json($forecast->map(fn($data) => $data['month']->format('F Y'))),
            datasets: [
                {
                    label: 'Projected Revenue',
                    data: @json($forecast->pluck('projected_revenue')),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush
