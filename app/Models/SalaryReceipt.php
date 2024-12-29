<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class SalaryReceipt extends Model
{
    use HasFactory;

    protected $table = 'salary_receipts';

    protected $fillable = [
        'salary_payment_id',
        'receipt_number',
        'salary_details',
        'payment_details',
        'generated_by',
        'generated_at',
        'pdf_path',
        'status'
    ];

    protected $casts = [
        'salary_details' => 'array',
        'payment_details' => 'array',
        'generated_at' => 'datetime'
    ];

    public function salaryPayment()
    {
        return $this->belongsTo(SalaryPayment::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class)->through('salaryPayment');
    }

    // Generate unique receipt number
    public static function generateReceiptNumber()
    {
        $prefix = 'SAL-RCP-' . date('Ym') . '-';
        $lastReceipt = self::where('receipt_number', 'like', $prefix . '%')
                          ->orderBy('receipt_number', 'desc')
                          ->first();
        
        $number = $lastReceipt ? intval(substr($lastReceipt->receipt_number, -4)) + 1 : 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    // Get the PDF URL
    public function getPdfUrl()
    {
        return $this->pdf_path ? Storage::url($this->pdf_path) : null;
    }

    // Store salary details snapshot
    public function storeSalaryDetails(SalaryPayment $payment)
    {
        $this->salary_details = [
            'employee' => [
                'id' => $payment->employee->id,
                'name' => $payment->employee->full_name,
                'employee_code' => $payment->employee->employee_code,
                'department' => $payment->employee->department,
                'designation' => $payment->employee->designation
            ],
            'payment_period' => [
                'month' => $payment->month,
                'year' => $payment->year
            ],
            'earnings' => [
                'base_salary' => $payment->base_salary,
                'overtime_pay' => $payment->overtime_pay,
                'bonus' => $payment->bonus,
                'total_earnings' => $payment->total_earnings
            ],
            'deductions' => [
                'tax_deduction' => $payment->tax_deduction,
                'other_deductions' => $payment->other_deductions,
                'total_deductions' => ($payment->tax_deduction + $payment->other_deductions)
            ],
            'net_salary' => $payment->net_salary,
            'payment_details' => [
                'payment_date' => $payment->payment_date,
                'payment_method' => $payment->payment_method,
                'transaction_reference' => $payment->transaction_reference
            ]
        ];
        return $this;
    }

    // Store payment transaction details
    public function storePaymentDetails(array $details)
    {
        $this->payment_details = array_merge($details, [
            'recorded_at' => now()->toDateTimeString()
        ]);
        return $this;
    }
}
