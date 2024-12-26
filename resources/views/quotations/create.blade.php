@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Generate Quotation for Project: {{ $project->name }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('quotations.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Client</label>
                                    <input type="text" class="form-control" value="{{ $project->client->company_name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Project Name</label>
                                    <input type="text" class="form-control" value="{{ $project->name }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4>Machine Details</h4>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Machine</th>
                                                    <th>Rate (per hour)</th>
                                                    <th>Estimated Hours</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($project->machines as $machine)
                                                <tr>
                                                    <td>{{ $machine->name }}</td>
                                                    <td>
                                                        <input type="number" name="rates[{{ $machine->id }}]" class="form-control rate" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="hours[{{ $machine->id }}]" class="form-control hours" required>
                                                    </td>
                                                    <td class="machine-total">0.00</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3">Total Amount</th>
                                                    <th id="grand-total">0.00</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Additional Notes</label>
                                    <textarea name="notes" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Generate Quotation</button>
                                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calculateTotals = () => {
            let grandTotal = 0;
            document.querySelectorAll('tbody tr').forEach(row => {
                const rate = parseFloat(row.querySelector('.rate').value) || 0;
                const hours = parseFloat(row.querySelector('.hours').value) || 0;
                const total = rate * hours;
                row.querySelector('.machine-total').textContent = total.toFixed(2);
                grandTotal += total;
            });
            document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
        };

        document.querySelectorAll('.rate, .hours').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });
    });
</script>
@endpush
@endsection
