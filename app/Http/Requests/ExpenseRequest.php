<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'project_id' => 'nullable|exists:projects,id',
            'expense_type' => 'required|in:Fuel,Maintenance,Repair,Transportation,Equipment,Miscellaneous',
            'amount' => 'required|numeric|min:0|max:1000000',
            'description' => 'nullable|string|max:500',
            'expense_date' => 'required|date|before_or_equal:today',
            'receipt_image' => 'nullable|image|max:5120' // 5MB max
        ];
    }

    public function messages()
    {
        return [
            'expense_type.in' => 'Invalid expense type selected',
            'amount.max' => 'Expense amount is too high',
            'receipt_image.max' => 'Receipt image must be less than 5MB'
        ];
    }
}
