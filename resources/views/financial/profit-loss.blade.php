@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">
            Profit & Loss Statement 
            @if($month)
                - {{ Carbon\Carbon::create()->month($month)->format('F') }}
            @endif
            {{ $year }}
        </h1>

        {{-- Filters --}}
        <form method="GET" action="{{ route('financial.profit-loss') }}" class="mb-6">
            <div class="flex space-x-4">
                <select name="year" class="form-select">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>

                <select name="month" class="form-select">
                    <option value="">Full Year</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    Generate Report
                </button>
            </div>
        </form>

        {{-- Financial Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-green-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-green-800">Total Revenue</h3>
                <p class="text-2xl font-bold text-green-900">
                    ₹{{ number_format($totalRevenue, 2) }}
                </p>
            </div>
            <div class="bg-red-100 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-red-800">Total Expenses</h3>
                <p class="text-2xl font-bold text-red-900">
                    ₹{{ number_format($totalExpenses, 2) }}
                </p>
            </div>
            <div class="{{ $netProfit >= 0 ? 'bg-green-100' : 'bg-red-100' }} p-4 rounded-lg">
                <h3 class="text-lg font-semibold {{ $netProfit >= 0 ? 'text-green-800' : 'text-red-800' }}">
                    Net Profit/Loss
                </h3>
                <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-900' : 'text-red-900' }}">
                    ₹{{ number_format($netProfit, 2) }}
                </p>
            </div>
        </div>

        {{-- Detailed Breakdown --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Revenue Breakdown --}}
            <div>
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Revenue Breakdown</h2>
                <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Source</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($revenue as $source => $amount)
                            <tr class="border-b">
                                <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $source) }}</td>
                                <td class="px-4 py-2 text-right text-green-600">
                                    ₹{{ number_format($amount, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Expenses Breakdown --}}
            <div>
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Expenses Breakdown</h2>
                <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 text-left">Category</th>
                            <th class="px-4 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                            <tr class="border-b">
                                <td class="px-4 py-2">{{ $expense->category }}</td>
                                <td class="px-4 py-2 text-right text-red-600">
                                    ₹{{ number_format($expense->total, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="px-4 py-2 font-semibold">Salary Expenses</td>
                            <td class="px-4 py-2 text-right text-red-600">
                                ₹{{ number_format($salaryExpenses, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
