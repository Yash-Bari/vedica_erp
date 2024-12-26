<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Machine;

class Expense extends Model
{
    use SoftDeletes;

    // Expense Types
    public const TYPE_MATERIAL = 'Material';
    public const TYPE_LABOR = 'Labor';
    public const TYPE_EQUIPMENT = 'Equipment';
    public const TYPE_TRANSPORTATION = 'Transportation';
    public const TYPE_MISCELLANEOUS = 'Miscellaneous';

    // Expense Categories
    public const CATEGORY_DIRECT = 'Direct';
    public const CATEGORY_INDIRECT = 'Indirect';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'amount',
        'date',
        'type',
        'category',
        'description',
        'vendor_name',
        'invoice_number',
        'payment_method',
        'employee_id',
        'machine_id',
        'budget_threshold'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'float',
    ];

    /**
     * Relationship: Expense belongs to a Project
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relationship: Expense can be associated with an Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship: Expense can be associated with a Machine
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Scope to filter expenses by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter expenses by category
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get expenses within a date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [
            Carbon::parse($startDate), 
            Carbon::parse($endDate)
        ]);
    }

    /**
     * Calculate total expenses for a given period
     */
    public static function calculateTotalExpenses($startDate = null, $endDate = null)
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->betweenDates($startDate, $endDate);
        }

        return $query->sum('amount');
    }

    /**
     * Check if an expense exceeds a budget threshold
     */
    public function exceedsBudgetThreshold($threshold)
    {
        return $this->amount > $threshold;
    }

    /**
     * Get formatted amount attribute
     */
    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }
}
