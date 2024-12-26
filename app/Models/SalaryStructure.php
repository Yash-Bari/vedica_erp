<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $table = 'salary_structures';

    protected $fillable = [
        'employee_id',
        'base_salary',
        'house_rent_allowance',
        'conveyance_allowance',
        'medical_allowance',
        'performance_bonus',
        'provident_fund',
        'professional_tax',
        'other_deductions',
        'net_salary_percentage',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'net_salary_percentage' => 'float'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Calculate total earnings
    public function calculateTotalEarnings()
    {
        return $this->base_salary + 
               $this->house_rent_allowance + 
               $this->conveyance_allowance + 
               $this->medical_allowance + 
               $this->performance_bonus;
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
        return $totalEarnings - $totalDeductions;
    }
}
