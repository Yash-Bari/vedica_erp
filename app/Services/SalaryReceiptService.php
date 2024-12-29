<?php

namespace App\Services;

use App\Models\SalaryPayment;
use App\Models\SalaryReceipt;
use Illuminate\Support\Facades\Storage;
use PDF;

class SalaryReceiptService
{
    public function generateReceipt(SalaryPayment $payment)
    {
        // Create receipt record
        $receipt = SalaryReceipt::create([
            'salary_payment_id' => $payment->id,
            'receipt_number' => SalaryReceipt::generateReceiptNumber(),
            'generated_by' => auth()->id(),
            'generated_at' => now()
        ]);

        // Store salary details
        $receipt->storeSalaryDetails($payment);

        // Generate PDF
        $pdf = PDF::loadView('salaries.receipts.pdf', [
            'receipt' => $receipt,
            'payment' => $payment,
            'employee' => $payment->employee
        ]);

        // Store PDF
        $pdfPath = 'salary-receipts/' . $receipt->receipt_number . '.pdf';
        Storage::put($pdfPath, $pdf->output());
        
        // Update receipt with PDF path
        $receipt->update(['pdf_path' => $pdfPath]);

        return $receipt;
    }

    public function getReceiptData(SalaryReceipt $receipt)
    {
        return [
            'receipt' => $receipt,
            'salary_details' => $receipt->salary_details,
            'payment_details' => $receipt->payment_details,
            'pdf_url' => $receipt->getPdfUrl()
        ];
    }
}
