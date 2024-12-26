<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo, 
    HasMany
};
use Carbon\Carbon;

class SalaryStructure extends Model
{
    protected $fillable = [
        'employee_id',
        'base_salary',
        'hourly_rate',
        'overtime_rate',
        'bonus_percentage'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Calculate overtime pay
    public function calculateOvertimePay(float $overtimeHours): float
    {
        return $this->overtime_rate * $overtimeHours;
    }

    // Calculate bonus
    public function calculateBonus(float $baseSalary): float
    {
        return $baseSalary * ($this->bonus_percentage / 100);
    }
}

class SalaryPayment extends Model
{
    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'base_salary',
        'overtime_hours',
        'overtime_pay',
        'bonus',
        'total_earnings',
        'tax_deduction',
        'other_deductions',
        'net_salary',
        'status',
        'payment_date',
        'payment_method',
        'transaction_reference'
    ];

    protected $dates = [
        'payment_date'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class, 'employee_id', 'employee_id');
    }

    // Process salary for an employee
    public static function processSalary(Employee $employee, int $year, string $month)
    {
        $salaryStructure = $employee->salaryStructure;
        
        if (!$salaryStructure) {
            throw new \Exception('No salary structure found for employee');
        }

        // Calculate project hours
        $projectHours = $employee->calculateProjectHours($year, $month);
        $baseSalary = $salaryStructure->base_salary;
        $overtimePay = $salaryStructure->calculateOvertimePay($projectHours['overtime_hours']);
        $bonus = $salaryStructure->calculateBonus($baseSalary);

        // Calculate deductions
        $deductions = $employee->deductions()
            ->whereYear('effective_date', $year)
            ->whereMonth('effective_date', $month)
            ->sum('amount');

        $totalEarnings = $baseSalary + $overtimePay + $bonus;
        $netSalary = $totalEarnings - $deductions;

        return self::create([
            'employee_id' => $employee->id,
            'year' => $year,
            'month' => $month,
            'base_salary' => $baseSalary,
            'overtime_hours' => $projectHours['overtime_hours'],
            'overtime_pay' => $overtimePay,
            'bonus' => $bonus,
            'total_earnings' => $totalEarnings,
            'other_deductions' => $deductions,
            'net_salary' => $netSalary,
            'status' => 'Processed'
        ]);
    }

    // Get salary summary for a period
    public static function getSalarySummary($startDate = null, $endDate = null)
    {
        $query = self::where('status', 'Paid');

        if ($startDate) {
            $query->where('payment_date', '>=', Carbon::parse($startDate));
        }

        if ($endDate) {
            $query->where('payment_date', '<=', Carbon::parse($endDate));
        }

        return [
            'total_salary_paid' => $query->sum('net_salary'),
            'average_salary' => $query->avg('net_salary'),
            'total_employees_paid' => $query->distinct('employee_id')->count('employee_id')
        ];
    }
}

class EmployeeDeduction extends Model
{
    protected $fillable = [
        'employee_id',
        'deduction_type',
        'amount',
        'effective_date',
        'reason'
    ];

    protected $dates = ['effective_date'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
