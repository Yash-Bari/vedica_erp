<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'profile_picture',
        'aadhaar_card',
        'driving_license',
        'permanent_address',
        'current_address',
        'role',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_ifsc_code',
        'username',
        'password',
        'employee_code'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'last_login_at' => 'datetime',
        'joining_date' => 'date',
        'status' => 'string',
    ];

    /**
     * Get the employee's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get the employee's status.
     */
    public function getStatusAttribute()
    {
        return $this->attributes['status'];
    }

    /**
     * Relationships
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'operator_id');
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function activeSalaryStructure(): HasOne
    {
        return $this->hasOne(SalaryStructure::class)->where('is_active', true);
    }

    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function latestSalaryPayment(): HasOne
    {
        return $this->hasOne(SalaryPayment::class)->latest();
    }

    /**
     * Check if employee has a specific role
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return $this->role === $roles;
    }

    /**
     * Check if the employee has finance access
     *
     * @return bool
     */
    public function hasFinanceAccess()
    {
        return $this->hasRole(['Admin', 'Finance']);
    }

    /**
     * Scope a query to only include active employees.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the current month's payment status for the employee
     */
    public function getCurrentMonthPaymentStatus(): string
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $payment = $this->salaryPayments()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->latest()
            ->first();

        if (!$payment) {
            return 'Pending';
        }

        return ucfirst($payment->status);
    }

    /**
     * Check if employee has pending payment for current month
     */
    public function hasPendingPayment(): bool
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return !$this->salaryPayments()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->exists();
    }

    /**
     * Get the current month's payment for the employee
     */
    public function getCurrentMonthPayment()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return $this->salaryPayments()
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->latest()
            ->first();
    }
}
