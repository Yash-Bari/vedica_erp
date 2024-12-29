<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Salary Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .receipt-number {
            color: #666;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .row {
            display: table-row;
        }
        .col {
            display: table-cell;
            padding: 5px;
        }
        .label {
            font-weight: bold;
            width: 150px;
        }
        .amount {
            text-align: right;
        }
        .total {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('app.name') }}</div>
        <div class="receipt-number">Receipt No: {{ $receipt->receipt_number }}</div>
        <div>Generated on: {{ $receipt->generated_at->format('d M Y, h:i A') }}</div>
    </div>

    <div class="section">
        <div class="section-title">Employee Details</div>
        <div class="grid">
            <div class="row">
                <div class="col label">Employee Name:</div>
                <div class="col">{{ $receipt->salary_details['employee']['name'] }}</div>
            </div>
            <div class="row">
                <div class="col label">Employee Code:</div>
                <div class="col">{{ $receipt->salary_details['employee']['employee_code'] }}</div>
            </div>
            <div class="row">
                <div class="col label">Department:</div>
                <div class="col">{{ $receipt->salary_details['employee']['department'] }}</div>
            </div>
            <div class="row">
                <div class="col label">Designation:</div>
                <div class="col">{{ $receipt->salary_details['employee']['designation'] }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Salary Period</div>
        <div class="grid">
            <div class="row">
                <div class="col label">Month:</div>
                <div class="col">{{ $receipt->salary_details['payment_period']['month'] }} {{ $receipt->salary_details['payment_period']['year'] }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Earnings</div>
        <div class="grid">
            <div class="row">
                <div class="col label">Base Salary:</div>
                <div class="col amount">₹{{ number_format($receipt->salary_details['earnings']['base_salary'], 2) }}</div>
            </div>
            @if($receipt->salary_details['earnings']['overtime_pay'] > 0)
            <div class="row">
                <div class="col label">Overtime Pay:</div>
                <div class="col amount">₹{{ number_format($receipt->salary_details['earnings']['overtime_pay'], 2) }}</div>
            </div>
            @endif
            @if($receipt->salary_details['earnings']['bonus'] > 0)
            <div class="row">
                <div class="col label">Bonus:</div>
                <div class="col amount">₹{{ number_format($receipt->salary_details['earnings']['bonus'], 2) }}</div>
            </div>
            @endif
            <div class="row total">
                <div class="col label">Total Earnings:</div>
                <div class="col amount">₹{{ number_format($receipt->salary_details['earnings']['total_earnings'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Deductions</div>
        <div class="grid">
            @if($receipt->salary_details['deductions']['tax_deduction'] > 0)
            <div class="row">
                <div class="col label">Tax Deduction:</div>
                <div class="col amount">₹{{ number_format($receipt->salary_details['deductions']['tax_deduction'], 2) }}</div>
            </div>
            @endif
            @if($receipt->salary_details['deductions']['other_deductions'] > 0)
            <div class="row">
                <div class="col label">Other Deductions:</div>
                <div class="col amount">₹{{ number_format($receipt->salary_details['deductions']['other_deductions'], 2) }}</div>
            </div>
            @endif
            <div class="row total">
                <div class="col label">Total Deductions:</div>
                <div class="col amount">₹{{ number_format($receipt->salary_details['deductions']['total_deductions'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Net Salary</div>
        <div class="grid">
            <div class="row total">
                <div class="col label">Net Payable:</div>
                <div class="col amount">₹{{ number_format($receipt->salary_details['net_salary'], 2) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Payment Details</div>
        <div class="grid">
            <div class="row">
                <div class="col label">Payment Date:</div>
                <div class="col">{{ \Carbon\Carbon::parse($receipt->salary_details['payment_details']['payment_date'])->format('d M Y') }}</div>
            </div>
            <div class="row">
                <div class="col label">Payment Method:</div>
                <div class="col">{{ $receipt->salary_details['payment_details']['payment_method'] }}</div>
            </div>
            @if($receipt->salary_details['payment_details']['transaction_reference'])
            <div class="row">
                <div class="col label">Transaction Ref:</div>
                <div class="col">{{ $receipt->salary_details['payment_details']['transaction_reference'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>{{ config('app.name') }} - {{ config('app.url') }}</p>
    </div>
</body>
</html>
