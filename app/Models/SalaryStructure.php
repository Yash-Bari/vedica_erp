<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'salary_structures';

    protected $fillable = [
        'employee_id',
        'base_salary',
        'hourly_rate',
        'overtime_rate',
        'bonus_percentage',
        'allowances',
        'deductions',
        'is_active',
        'effective_date'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'datetime',
        'allowances' => 'json',
        'deductions' => 'json',
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'bonus_percentage' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Calculate total earnings including allowances
    public function calculateTotalEarnings()
    {
        return $this->base_salary +
            $this->house_rent_allowance +
            $this->conveyance_allowance +
            $this->medical_allowance +
            $this->performance_bonus +
            ($this->base_salary * ($this->bonus_percentage / 100));
    }

    // Calculate total deductions
    public function calculateTotalDeductions()
    {
        return $this->provident_fund +
            $this->professional_tax +
            $this->other_deductions;
    }

    // Calculate net salary
    public function calculateNetSalary()
    {
        $totalEarnings = $this->calculateTotalEarnings();
        $totalDeductions = $this->calculateTotalDeductions();
        $netSalary = $totalEarnings - $totalDeductions;
        
        // Apply net salary percentage
        return round($netSalary * ($this->net_salary_percentage / 100), 2);
    }

    /**
     * Calculate total allowances for this salary structure
     */
    public function calculateTotalAllowances(): float
    {
        $allowances = json_decode($this->allowances, true) ?? [];
        return array_sum(array_values($allowances));
    }

    /**
     * Calculate total deductions for this salary structure
     */
    public function calculateTotalDeductionsFromJson(): float
    {
        $deductions = json_decode($this->deductions, true) ?? [];
        return array_sum(array_values($deductions));
    }

    /**
     * Calculate net salary for this salary structure
     */
    public function calculateNetSalaryFromJson(): float
    {
        return $this->base_salary + $this->calculateTotalAllowances() - $this->calculateTotalDeductionsFromJson();
    }

    /**
     * Get formatted allowances array
     */
    public function getAllowancesArray(): array
    {
        return json_decode($this->allowances, true) ?? [];
    }

    /**
     * Get formatted deductions array
     */
    public function getDeductionsArray(): array
    {
        return json_decode($this->deductions, true) ?? [];
    }

    /**
     * Calculate the total allowances from the JSON structure
     */
    public function calculateTotalAllowancesFromJson()
    {
        $allowances = json_decode($this->allowances, true) ?? [];
        return array_sum($allowances);
    }

    /**
     * Calculate the total deductions from the JSON structure
     */
    public function calculateTotalDeductionsFromJsonMethod()
    {
        $deductions = json_decode($this->deductions, true) ?? [];
        return array_sum($deductions);
    }

    /**
     * Calculate the net salary
     */
    public function calculateNetSalaryFromJsonMethod()
    {
        return $this->base_salary + $this->calculateTotalAllowancesFromJson() - $this->calculateTotalDeductionsFromJsonMethod();
    }

    // Scope for active salary structures
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // When creating a new salary structure, deactivate all other structures for this employee
    protected static function booted()
    {
        static::creating(function ($salaryStructure) {
            if ($salaryStructure->is_active) {
                static::where('employee_id', $salaryStructure->employee_id)
                    ->where('id', '!=', $salaryStructure->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}
