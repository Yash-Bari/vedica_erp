<!DOCTYPE html>
<html>
<head>
    <title>Financial Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .summary-item {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
            flex-grow: 1;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <h1>Financial Report - {{ now()->format('Y-m-d') }}</h1>

    @if($start_date || $end_date)
    <p>
        Report Period: 
        {{ $start_date ? 'From ' . $start_date : 'From Beginning' }} 
        {{ $end_date ? 'To ' . $end_date : 'To Present' }}
    </p>
    @endif

    <div class="summary">
        <div class="summary-item">
            <h3>Total Projects</h3>
            <p>{{ $projects->count() }}</p>
        </div>
        <div class="summary-item">
            <h3>Total Project Revenue</h3>
            <p>₹{{ number_format($projects->sum('total_revenue'), 2) }}</p>
        </div>
        <div class="summary-item">
            <h3>Total Expenses</h3>
            <p>₹{{ number_format($expenses->sum('amount'), 2) }}</p>
        </div>
    </div>

    <h2>Projects</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Platform</th>
                <th>Total Revenue</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{ $project->name }}</td>
                <td>{{ $project->platform }}</td>
                <td>₹{{ number_format($project->total_revenue, 2) }}</td>
                <td>{{ $project->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Expenses</h2>
    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Category</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->category }}</td>
                <td>₹{{ number_format($expense->amount, 2) }}</td>
                <td>{{ $expense->date->format('Y-m-d') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
