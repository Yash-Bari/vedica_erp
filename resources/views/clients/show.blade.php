@extends('layouts.app')

@section('title', 'Client Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <!-- Client Details Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Client Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Client
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Company Name</dt>
                                <dd>{{ $client->name }}</dd>

                                <dt>Contact Person</dt>
                                <dd>{{ $client->contact_person ?? 'N/A' }}</dd>

                                <dt>Email</dt>
                                <dd>{{ $client->email ?? 'N/A' }}</dd>

                                <dt>Phone</dt>
                                <dd>{{ $client->phone }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Source</dt>
                                <dd>{{ $client->source }}</dd>

                                <dt>Address</dt>
                                <dd>{{ $client->address ?? 'N/A' }}</dd>

                                <dt>Created At</dt>
                                <dd>{{ $client->created_at->format('d M Y') }}</dd>

                                <dt>Updated At</dt>
                                <dd>{{ $client->updated_at->format('d M Y') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Projects Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Recent Projects</h3>
                    <div class="card-tools">
                        <a href="{{ route('projects.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> New Project
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Created Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentProjects as $project)
                                <tr>
                                    <td>{{ $project->name }}</td>
                                    <td>{{ $project->created_at->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $project->status == 'Active' ? 'success' : 'warning' }}">
                                            {{ $project->status ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('projects.show', $project->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No projects found for this client.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Notes Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Address</h3>
                </div>
                <div class="card-body">
                    {{ $client->address ?? 'No address available.' }}
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group">
                        <a href="{{ route('projects.create') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus mr-2"></i> Create New Project
                        </a>
                        <a href="{{ route('clients.edit', $client->id) }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-edit mr-2"></i> Edit Client Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
