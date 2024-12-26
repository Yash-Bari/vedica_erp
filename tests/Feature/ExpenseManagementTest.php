<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $employee;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->employee = User::factory()->create(['role' => 'employee']);

        // Create a test project
        $this->project = Project::factory()->create();
    }

    /** @test */
    public function admin_can_create_expense()
    {
        $this->actingAs($this->admin);

        $expenseData = [
            'project_id' => $this->project->id,
            'amount' => 1000.50,
            'date' => now()->format('Y-m-d'),
            'type' => 'Material',
            'category' => 'Direct',
            'description' => 'Test expense description',
            'vendor_name' => 'Test Vendor',
            'payment_method' => 'Cash'
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'project_id' => $this->project->id,
            'amount' => 1000.50,
            'type' => 'Material'
        ]);
    }

    /** @test */
    public function manager_can_create_expense()
    {
        $this->actingAs($this->manager);

        $expenseData = [
            'project_id' => $this->project->id,
            'amount' => 2000.75,
            'date' => now()->format('Y-m-d'),
            'type' => 'Labor',
            'category' => 'Indirect',
            'description' => 'Manager expense description'
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'project_id' => $this->project->id,
            'amount' => 2000.75,
            'type' => 'Labor'
        ]);
    }

    /** @test */
    public function employee_cannot_create_expense()
    {
        $this->actingAs($this->employee);

        $expenseData = [
            'project_id' => $this->project->id,
            'amount' => 500.25,
            'date' => now()->format('Y-m-d'),
            'type' => 'Material',
            'category' => 'Direct'
        ];

        $response = $this->post(route('expenses.store'), $expenseData);

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_update_expense()
    {
        $this->actingAs($this->admin);

        $expense = Expense::factory()->create([
            'project_id' => $this->project->id,
            'amount' => 1500.00,
            'type' => 'Material'
        ]);

        $updatedData = [
            'project_id' => $this->project->id,
            'amount' => 2500.00,
            'date' => now()->format('Y-m-d'),
            'type' => 'Equipment',
            'category' => 'Direct',
            'description' => 'Updated expense description'
        ];

        $response = $this->put(route('expenses.update', $expense), $updatedData);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 2500.00,
            'type' => 'Equipment'
        ]);
    }

    /** @test */
    public function manager_can_update_own_project_expense()
    {
        // Create a project managed by the manager
        $managedProject = Project::factory()->create(['manager_id' => $this->manager->id]);

        $this->actingAs($this->manager);

        $expense = Expense::factory()->create([
            'project_id' => $managedProject->id,
            'amount' => 1000.00
        ]);

        $updatedData = [
            'project_id' => $managedProject->id,
            'amount' => 1500.00,
            'date' => now()->format('Y-m-d'),
            'type' => 'Labor',
            'category' => 'Indirect'
        ];

        $response = $this->put(route('expenses.update', $expense), $updatedData);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 1500.00
        ]);
    }

    /** @test */
    public function manager_cannot_update_other_project_expense()
    {
        $this->actingAs($this->manager);

        $expense = Expense::factory()->create([
            'project_id' => $this->project->id
        ]);

        $updatedData = [
            'amount' => 2000.00,
            'date' => now()->format('Y-m-d'),
            'type' => 'Equipment',
            'category' => 'Direct'
        ];

        $response = $this->put(route('expenses.update', $expense), $updatedData);

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_delete_expense()
    {
        $this->actingAs($this->admin);

        $expense = Expense::factory()->create([
            'project_id' => $this->project->id
        ]);

        $response = $this->delete(route('expenses.destroy', $expense));

        $response->assertRedirect();
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    /** @test */
    public function expense_validation_fails_for_invalid_data()
    {
        $this->actingAs($this->admin);

        $invalidExpenseData = [
            'amount' => -100, // Invalid negative amount
            'date' => '2025-01-01', // Future date
            'type' => 'Invalid Type',
            'category' => 'Non-Existent Category'
        ];

        $response = $this->post(route('expenses.store'), $invalidExpenseData);

        $response->assertSessionHasErrors([
            'amount',
            'date',
            'type',
            'category'
        ]);
    }

    /** @test */
    public function can_generate_expense_report()
    {
        $this->actingAs($this->admin);

        // Create multiple expenses
        Expense::factory()->count(5)->create([
            'project_id' => $this->project->id,
            'date' => now()
        ]);

        $response = $this->get(route('expenses.report', [
            'start_date' => now()->subMonth(),
            'end_date' => now()
        ]));

        $response->assertOk();
        $response->assertViewHas(['totalExpenses', 'expensesByType']);
    }

    /** @test */
    public function can_export_expenses_to_csv()
    {
        $this->actingAs($this->admin);

        // Create multiple expenses
        Expense::factory()->count(10)->create([
            'project_id' => $this->project->id
        ]);

        $response = $this->get(route('expenses.export', [
            'start_date' => now()->subMonth(),
            'end_date' => now()
        ]));

        $response->assertOk();
        $response->assertDownload();
    }
}
