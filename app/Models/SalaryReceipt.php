<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryReceipt extends Model
{
    use HasFactory;

    protected $table = 'salary_receipts';

    protected $fillable = [
        'salary_payment_id',
        'employee_id',
        'receipt_number',
        'total_earnings',
        'total_deductions',
        'net_salary',
        'payment_date',
        'payment_method',
        'remarks'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'total_earnings' => 'float',
        'total_deductions' => 'float',
        'net_salary' => 'float'
    ];

    public function salaryPayment()
    {
        return $this->belongsTo(SalaryPayment::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Generate unique receipt number
    public static function generateReceiptNumber()
    {
        $lastReceipt = self::latest()->first();
        $number = $lastReceipt ? intval(substr($lastReceipt->receipt_number, 3)) + 1 : 1;
        return 'RCP' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}
