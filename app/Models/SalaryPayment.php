<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $table = 'salary_payments';

    protected $fillable = [
        'employee_id', 
        'year', 
        'month', 
        'basic_salary', 
        'allowances', 
        'deductions', 
        'net_salary', 
        'payment_date', 
        'payment_method', 
        'status',
        'salary_structure_id'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'float',
        'allowances' => 'float',
        'deductions' => 'float',
        'net_salary' => 'float'
    ];

    // Relationship with Employee model
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // Relationship with SalaryStructure
    public function salaryStructure()
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    // Relationship with SalaryReceipt
    public function salaryReceipt()
    {
        return $this->hasOne(SalaryReceipt::class, 'salary_payment_id');
    }

    // Relationship with SalaryReceipt
    public function receipt()
    {
        return $this->hasOne(SalaryReceipt::class);
    }

    // Scope for filtering
    public function scopeFilterByYearAndMonth($query, $year = null, $month = null)
    {
        if ($year) {
            $query->where('year', $year);
        }

        if ($month) {
            $query->where('month', $month);
        }

        return $query;
    }

    // Scopes
    public function scopeProcessedThisMonth($query, $employeeId)
    {
        $currentMonth = Carbon::now()->format('F');
        $currentYear = Carbon::now()->year;

        return $query->where('employee_id', $employeeId)
                     ->where('month', $currentMonth)
                     ->where('year', $currentYear)
                     ->whereHas('salaryReceipt');
    }

    // Static method to process monthly salaries
    public static function processMonthlySalaries($year, $month)
    {
        // Get all active employees
        $employees = Employee::where('status', 'Active')->get();

        foreach ($employees as $employee) {
            // Find salary structure
            $salaryStructure = SalaryStructure::where('employee_id', $employee->id)
                ->where('is_active', true)
                ->first();

            if (!$salaryStructure) {
                continue; // Skip if no active salary structure
            }

            // Calculate salary components
            $basicSalary = $salaryStructure->base_salary;
            $allowances = $salaryStructure->calculateTotalEarnings() - $basicSalary;
            $deductions = $salaryStructure->calculateTotalDeductions();
            $netSalary = $salaryStructure->calculateNetSalary();

            // Create salary payment record
            $salaryPayment = self::create([
                'employee_id' => $employee->id,
                'year' => $year,
                'month' => $month,
                'basic_salary' => $basicSalary,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'net_salary' => $netSalary,
                'salary_structure_id' => $salaryStructure->id,
                'status' => 'Pending',
                'payment_date' => null
            ]);

            // Optional: Trigger notification or further processing
        }

        return true;
    }

    // Method to mark salary as paid
    public function markAsPaid($paymentMethod)
    {
        $this->status = 'Paid';
        $this->payment_date = now();
        $this->payment_method = $paymentMethod;
        $this->save();

        // Generate receipt
        $receipt = SalaryReceipt::create([
            'salary_payment_id' => $this->id,
            'employee_id' => $this->employee_id,
            'receipt_number' => SalaryReceipt::generateReceiptNumber(),
            'total_earnings' => $this->basic_salary + $this->allowances,
            'total_deductions' => $this->deductions,
            'net_salary' => $this->net_salary,
            'payment_date' => $this->payment_date,
            'payment_method' => $paymentMethod,
            'remarks' => "Salary for {$this->month} {$this->year}"
        ]);

        return $receipt;
    }

    // Accessor for formatted net salary
    public function getFormattedNetSalaryAttribute()
    {
        return number_format($this->net_salary, 2);
    }
}
