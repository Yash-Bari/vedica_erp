<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        // Combine projects and expenses
        $combinedData = collect();

        // Add project data
        $this->data['projects']->each(function ($project) use ($combinedData) {
            $combinedData->push([
                'type' => 'Project',
                'name' => $project->name,
                'platform' => $project->platform,
                'total_revenue' => $project->total_revenue,
                'status' => $project->status,
                'created_at' => $project->created_at,
            ]);
        });

        // Add expense data
        $this->data['expenses']->each(function ($expense) use ($combinedData) {
            $combinedData->push([
                'type' => 'Expense',
                'name' => $expense->description,
                'platform' => 'N/A',
                'total_revenue' => -$expense->amount,
                'status' => $expense->category,
                'created_at' => $expense->date,
            ]);
        });

        return $combinedData;
    }

    public function headings(): array
    {
        return [
            'Type',
            'Name',
            'Platform',
            'Amount',
            'Status/Category',
            'Date'
        ];
    }

    public function map($row): array
    {
        return [
            $row['type'],
            $row['name'],
            $row['platform'],
            $row['total_revenue'],
            $row['status'],
            $row['created_at']
        ];
    }
}
