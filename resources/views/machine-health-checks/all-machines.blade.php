@extends('layouts.app')

@section('title', 'Machine Health Checks')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Machine Health Checks</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Machine Name</th>
                                    <th>Model</th>
                                    <th>Last Health Check</th>
                                    <th>Total Checks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($machines as $machine)
                                <tr>
                                    <td>{{ $machine->name }}</td>
                                    <td>{{ $machine->model }}</td>
                                    <td>
                                        @if($machine->healthChecks->isNotEmpty())
                                            {{ $machine->healthChecks->sortByDesc('created_at')->first()->created_at->diffForHumans() }}
                                        @else
                                            No health checks yet
                                        @endif
                                    </td>
                                    <td>{{ $machine->health_checks_count }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('machine-health-checks.create', $machine->id) }}" 
                                               class="btn btn-primary btn-sm" 
                                               title="Perform Health Check">
                                                <i class="fas fa-plus"></i> New Check
                                            </a>
                                            <a href="{{ route('machine-health-checks.index', $machine->id) }}" 
                                               class="btn btn-info btn-sm"
                                               title="View Health Check History">
                                                <i class="fas fa-history"></i> History
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No active machines found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($machines->hasPages())
                <div class="card-footer">
                    {{ $machines->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .btn-group {
        display: flex;
        gap: 0.5rem;
    }
    .table td {
        vertical-align: middle;
    }
</style>
@endpush
