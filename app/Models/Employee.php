<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        // Personal Information
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'date_of_birth',
        'gender',
        
        // Profile and Documents
        'profile_picture',
        'aadhaar_card',
        'driving_license',
        
        // Address Information
        'permanent_address',
        'current_address',
        
        // Employment Details
        'role',
        'status',
        
        // Bank Details
        'bank_name',
        'bank_account_number',
        'bank_ifsc_code',
        
        // Authentication
        'username',
        'password',
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
    ];

    /**
     * Get the employee's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
{
    return "{$this->first_name} {$this->last_name}";
}

    /**
     * Relationships
     */
    public function projects()
{
    return $this->hasMany(Project::class, 'operator_id');
}

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    public function activeSalaryStructure()
    {
        return $this->hasOne(SalaryStructure::class)
            ->where('is_active', true)
            ->latest('created_at');
    }

    /**
     * Check if employee has a specific role
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
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
        return $query->where('status', 'Active');
    }
    
}
