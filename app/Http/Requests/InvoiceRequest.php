<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'client_id' => 'required|exists:clients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after:invoice_date',
            'total_amount' => 'required|numeric|min:0|max:1000000',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:1|max:1000',
            'items.*.unit_price' => 'required|numeric|min:0|max:100000'
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'At least one invoice item is required',
            'items.*.quantity.max' => 'Quantity is too high',
            'items.*.unit_price.max' => 'Unit price is too high'
        ];
    }
}
