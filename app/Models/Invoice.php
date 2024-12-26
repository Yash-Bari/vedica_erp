<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo, 
    HasMany
};
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    protected $fillable = [
        'project_id',
        'client_id',
        'created_by',
        'invoice_number',
        'invoice_date',
        'due_date',
        'notes',
        'subtotal',
        'gst_amount',
        'total_amount',
        'status'
    ];

    protected $dates = [
        'invoice_date',
        'due_date',
        'created_at',
        'updated_at'
    ];

    const GST_RATE = 0.18; // 18% GST

    protected $casts = [
        'subtotal' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'invoice_date' => 'date',
        'due_date' => 'date'
    ];

    protected static function booted()
    {
        static::creating(function ($invoice) {
            $invoice->invoice_number = self::generateInvoiceNumber();
        });
    }

    public static function generateInvoiceNumber()
    {
        $year = now()->year;
        $lastInvoice = self::where('invoice_number', 'LIKE', "INV-{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = $lastInvoice 
            ? intval(substr($lastInvoice->invoice_number, -4)) + 1 
            : 1;

        return sprintf("INV-%s-%04d", $year, $sequence);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Calculate total paid amount
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    // Check if invoice is fully paid
    public function isFullyPaid()
    {
        return $this->payments()->sum('amount') >= $this->total_amount;
    }

    // Mark invoice as paid
    public function markAsPaid()
    {
        $this->update([
            'status' => 'Paid'
        ]);

        // Update project invoice status
        if ($this->project) {
            $this->project->update([
                'invoice_status' => Project::INVOICE_STATUS_PAID
            ]);
        }
    }

    // Generate PDF invoice
    public function generatePDF()
    {
        $pdf = PDF::loadView('invoices.pdf', ['invoice' => $this]);
        $filename = "invoice_{$this->invoice_number}.pdf";
        $path = "invoices/{$filename}";
        
        Storage::put($path, $pdf->output());
        
        $this->invoice_pdf = $path;
        $this->save();

        return $path;
    }

    // Scope for overdue invoices
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'Paid')
            ->where('due_date', '<', now());
    }

    // Get total revenue in a date range
    public static function getTotalRevenue($startDate, $endDate)
    {
        return static::whereBetween('invoice_date', [$startDate, $endDate])
                    ->sum('total_amount');
    }

    public function calculateAmounts($subtotal = null)
    {
        if ($subtotal === null) {
            $subtotal = $this->project->revenue ?? 0;
        }
        
        $this->subtotal = $subtotal;
        $this->gst_amount = $subtotal * self::GST_RATE;
        $this->total_amount = $subtotal + $this->gst_amount;
        
        return [
            'subtotal' => $this->subtotal,
            'gst_amount' => $this->gst_amount,
            'total_amount' => $this->total_amount
        ];
    }
}

// Supporting Models
class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 
        'description', 
        'quantity', 
        'unit_price', 
        'total_price'
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 
        'amount', 
        'payment_date', 
        'payment_method', 
        'transaction_reference', 
        'notes'
    ];

    protected $dates = ['payment_date'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
