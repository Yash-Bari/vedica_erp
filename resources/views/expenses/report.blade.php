@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Expense Report</h1>
            <div class="flex items-center space-x-4">
                <button onclick="window.print()" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Print Report
                </button>
                <button id="exportPDF" 
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Export PDF
                </button>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">Expense Summary</h2>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <p class="text-gray-700">
                            <strong>Total Expenses:</strong> 
                            ₹{{ number_format($totalExpenses, 2) }}
                        </p>
                        <p class="text-gray-700">
                            <strong>Report Period:</strong> 
                            {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} - 
                            {{ Carbon\Carbon::parse($endDate)->format('d M Y') }}
                        </p>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold mb-4">Expense Breakdown</h2>
                    <canvas id="expenseChart" width="400" height="200"></canvas>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4">Detailed Expense Breakdown</h2>
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Expense Type
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total Amount
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Percentage
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($expensesByType as $expense)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                {{ $expense->expense_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                ₹{{ number_format($expense->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-gray-500">
                                {{ number_format(($expense->total_amount / $totalExpenses) * 100, 2) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('expenseChart').getContext('2d');
        const expenseData = @json($expensesByType);

        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: expenseData.map(item => item.expense_type),
                datasets: [{
                    data: expenseData.map(item => item.total_amount),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: true,
                        text: 'Expense Breakdown'
                    }
                }
            }
        });

        // PDF Export (Placeholder - integrate with actual PDF generation library)
        document.getElementById('exportPDF').addEventListener('click', function() {
            alert('PDF export functionality to be implemented');
        });
    });
</script>
@endpush
@endsection
