<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationMachineDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'machine_id',
        'rate_per_hour',
        'estimated_hours',
        'total'
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
