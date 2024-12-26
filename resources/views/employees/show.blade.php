@extends('layouts.app')

@section('title', 'Employee Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employee Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Employee
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Name</dt>
                                <dd>{{ $employee->name }}</dd>

                                <dt>Email</dt>
                                <dd>{{ $employee->email }}</dd>

                                <dt>Phone</dt>
                                <dd>{{ $employee->phone_number }}</dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Role</dt>
                                <dd>{{ $employee->role }}</dd>

                                <dt>Status</dt>
                                <dd>
                                    <span class="badge badge-{{ $employee->status == 'Active' ? 'success' : 'danger' }}">
                                        {{ $employee->status }}
                                    </span>
                                </dd>

                                <dt>Joined Date</dt>
                                <dd>{{ $employee->created_at->format('d M Y') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
