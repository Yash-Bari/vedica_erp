<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalaryRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust based on your authorization logic
    }

    public function rules()
    {
        return [
            'base_salary' => 'required|numeric|min:0|max:1000000',
            'hourly_rate' => 'required|numeric|min:0|max:10000',
            'overtime_rate' => 'required|numeric|min:0|max:10000',
            'bonus_percentage' => 'nullable|numeric|min:0|max:100',
            'deduction_type' => 'nullable|string|max:100',
            'deduction_amount' => 'nullable|numeric|min:0|max:100000',
            'effective_date' => 'nullable|date'
        ];
    }

    public function messages()
    {
        return [
            'base_salary.max' => 'Base salary is too high',
            'hourly_rate.max' => 'Hourly rate is too high',
            'overtime_rate.max' => 'Overtime rate is too high',
            'bonus_percentage.max' => 'Bonus percentage cannot exceed 100%',
            'deduction_amount.max' => 'Deduction amount is too high'
        ];
    }
}
