@extends('layouts.app')

@section('title', 'Finance Dashboard')

@push('styles')
<style>
    .small-box {
        @apply relative rounded-lg p-4 mb-4 overflow-hidden transition-all duration-300 hover:shadow-lg;
    }
    .small-box .inner {
        @apply p-3;
    }
    .small-box .inner h3 {
        @apply text-2xl font-bold mb-1;
    }
    .small-box .inner p {
        @apply text-sm;
    }
    .small-box .icon {
        @apply absolute right-4 top-4 text-4xl opacity-30;
    }
    .small-box .small-box-footer {
        @apply block text-center py-2 text-sm text-white bg-black bg-opacity-20 hover:bg-opacity-30;
    }
    .bg-info {
        @apply bg-blue-500 text-white;
    }
    .bg-success {
        @apply bg-green-500 text-white;
    }
    .bg-warning {
        @apply bg-yellow-500 text-white;
    }
    .bg-danger {
        @apply bg-red-500 text-white;
    }
    .card {
        @apply bg-white rounded-lg shadow-sm mb-4 overflow-hidden;
    }
    .card-header {
        @apply px-4 py-3 bg-gray-50 border-b border-gray-200;
    }
    .card-title {
        @apply text-lg font-semibold text-gray-800;
    }
    .card-body {
        @apply p-4;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Revenue Overview -->
        <div class="small-box bg-info">
            <div class="inner">
                <h3>₹{{ number_format($monthlyRevenue, 2) }}</h3>
                <p>Monthly Revenue</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <a href="{{ route('financial.analytics') }}" class="small-box-footer">
                View Analytics <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>

        <!-- Expenses -->
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>₹{{ number_format($monthlyExpenses, 2) }}</h3>
                <p>Monthly Expenses</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <a href="{{ route('expenses.index') }}" class="small-box-footer">
                Manage Expenses <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>

        <!-- Pending Invoices -->
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $unpaidInvoices }}</h3>
                <p>Pending Invoices</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-invoice-dollar"></i>
            </div>
            <a href="{{ route('invoices.index') }}" class="small-box-footer">
                View Invoices <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>

        <!-- Profit/Loss -->
        <div class="small-box {{ $profitLoss >= 0 ? 'bg-success' : 'bg-danger' }}">
            <div class="inner">
                <h3>₹{{ number_format($profitLoss, 2) }}</h3>
                <p>Net Profit/Loss</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <a href="{{ route('financial.dashboard') }}" class="small-box-footer">
                View Details <i class="fas fa-arrow-circle-right ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Financial Management -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-money-bill-wave mr-2"></i>Financial Management
                </h3>
            </div>
            <div class="card-body space-y-3">
                <a href="{{ route('expenses.create') }}" 
                   class="block px-4 py-2 text-sm text-white bg-blue-500 rounded hover:bg-blue-600 text-center">
                    <i class="fas fa-plus mr-2"></i>New Expense
                </a>
                <a href="{{ route('invoices.create') }}" 
                   class="block px-4 py-2 text-sm text-white bg-green-500 rounded hover:bg-green-600 text-center">
                    <i class="fas fa-file-invoice mr-2"></i>Create Invoice
                </a>
                <a href="{{ route('financial.analytics') }}" 
                   class="block px-4 py-2 text-sm text-white bg-purple-500 rounded hover:bg-purple-600 text-center">
                    <i class="fas fa-chart-bar mr-2"></i>View Analytics
                </a>
            </div>
        </div>

        <!-- Salary Management -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-money-check-alt mr-2"></i>Salary Management
                </h3>
            </div>
            <div class="card-body space-y-3">
                <a href="{{ route('salaries.index') }}" 
                   class="block px-4 py-2 text-sm text-white bg-blue-500 rounded hover:bg-blue-600 text-center">
                    <i class="fas fa-list mr-2"></i>View Salaries
                </a>
                <a href="{{ route('salaries.structure.create') }}" 
                   class="block px-4 py-2 text-sm text-white bg-green-500 rounded hover:bg-green-600 text-center">
                    <i class="fas fa-plus mr-2"></i>New Salary Structure
                </a>
                <a href="{{ route('salaries.report') }}" 
                   class="block px-4 py-2 text-sm text-white bg-yellow-500 rounded hover:bg-yellow-600 text-center">
                    <i class="fas fa-file-alt mr-2"></i>Salary Reports
                </a>
            </div>
        </div>

        <!-- Reports & Analytics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>Reports & Analytics
                </h3>
            </div>
            <div class="card-body space-y-3">
                <a href="{{ route('invoices.report') }}" 
                   class="block px-4 py-2 text-sm text-white bg-blue-500 rounded hover:bg-blue-600 text-center">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>Invoice Reports
                </a>
                <a href="{{ route('financial.analytics.export') }}" 
                   class="block px-4 py-2 text-sm text-white bg-green-500 rounded hover:bg-green-600 text-center">
                    <i class="fas fa-file-export mr-2"></i>Export Analytics
                </a>
                <a href="{{ route('financial.dashboard') }}" 
                   class="block px-4 py-2 text-sm text-white bg-indigo-500 rounded hover:bg-indigo-600 text-center">
                    <i class="fas fa-tachometer-alt mr-2"></i>Financial Overview
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header flex justify-between items-center">
            <h3 class="card-title">
                <i class="fas fa-history mr-2"></i>Recent Financial Activity
            </h3>
            <a href="#" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
        </div>
        <div class="card-body">
            @if(isset($recentActivities) && count($recentActivities) > 0)
                <div class="space-y-4">
                    @foreach($recentActivities as $activity)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 rounded-full 
                                    @if($activity->type === 'expense') bg-red-100
                                    @elseif($activity->type === 'invoice') bg-green-100
                                    @else bg-blue-100 @endif">
                                    <i class="fas fa-{{ $activity->type === 'expense' ? 'minus' : ($activity->type === 'invoice' ? 'plus' : 'sync') }} 
                                        @if($activity->type === 'expense') text-red-600
                                        @elseif($activity->type === 'invoice') text-green-600
                                        @else text-blue-600 @endif"></i>
                                </div>
                                <div>
                                    <p class="font-medium">{{ $activity->description }}</p>
                                    <p class="text-sm text-gray-600">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-medium 
                                    @if($activity->type === 'expense') text-red-600
                                    @elseif($activity->type === 'invoice') text-green-600
                                    @else text-blue-600 @endif">
                                    ₹{{ number_format($activity->amount, 2) }}
                                </p>
                                <p class="text-sm text-gray-600">{{ $activity->reference }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-6 text-gray-500">
                    No recent activities to display
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
