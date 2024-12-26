<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $admin;
    protected $manager;
    protected $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->supervisor = User::factory()->create(['role' => 'supervisor']);
    }

    /** @test */
    public function test_project_creation()
    {
        $this->actingAs($this->admin);

        $projectData = [
            'name' => $this->faker->sentence,
            'client_name' => $this->faker->company,
            'location' => $this->faker->city,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
            'status' => 'Pending',
            'priority' => 'Medium',
            'budget' => $this->faker->randomFloat(2, 1000, 100000),
            'description' => $this->faker->paragraph,
            'supervisor_id' => $this->supervisor->id,
            'manager_id' => $this->manager->id,
        ];

        $response = $this->post(route('projects.store'), $projectData);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'name' => $projectData['name'],
            'client_name' => $projectData['client_name'],
        ]);
    }

    /** @test */
    public function test_project_progress_calculation()
    {
        $this->actingAs($this->admin);

        // Create a project with a known timeline
        $startDate = now()->subMonths(2);
        $endDate = now()->addMonths(1);

        $project = Project::factory()->create([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'In Progress'
        ]);

        // Simulate time progression and check progress
        Carbon::setTestNow($startDate->addMonths(1.5)); // Halfway through project
        $this->assertEquals(50, $project->progressPercentage());

        // Test completed project
        $project->status = 'Completed';
        $this->assertEquals(100, $project->progressPercentage());

        // Test pending project
        $project->status = 'Pending';
        $this->assertEquals(0, $project->progressPercentage());
    }

    /** @test */
    public function test_project_overdue_detection()
    {
        $this->actingAs($this->admin);

        // Create an overdue project
        $project = Project::factory()->create([
            'start_date' => now()->subMonths(3),
            'end_date' => now()->subMonth(),
            'status' => 'In Progress'
        ]);

        $this->assertTrue($project->isOverdue());

        // Create a non-overdue project
        $project = Project::factory()->create([
            'start_date' => now()->subMonths(1),
            'end_date' => now()->addMonths(2),
            'status' => 'In Progress'
        ]);

        $this->assertFalse($project->isOverdue());
    }

    /** @test */
    public function test_project_health_calculation()
    {
        $this->actingAs($this->admin);

        // Create a project with good health
        $project = Project::factory()->create([
            'start_date' => now()->subMonths(1),
            'end_date' => now()->addMonths(2),
            'status' => 'In Progress',
            'budget' => 10000
        ]);

        // Add some expenses within budget
        $project->expenses()->create([
            'amount' => 5000,
            'date' => now(),
            'type' => 'Material',
            'description' => 'Test expense'
        ]);

        $this->assertEquals('Good', $project->projectHealth());

        // Create an at-risk project
        $riskProject = Project::factory()->create([
            'start_date' => now()->subMonths(2),
            'end_date' => now()->subMonth(),
            'status' => 'In Progress',
            'budget' => 10000
        ]);

        $riskProject->expenses()->create([
            'amount' => 15000,
            'date' => now(),
            'type' => 'Material',
            'description' => 'Overspent expense'
        ]);

        $this->assertEquals('At Risk', $riskProject->projectHealth());
    }

    /** @test */
    public function test_project_scopes()
    {
        $this->actingAs($this->admin);

        // Create projects with different statuses
        Project::factory()->create(['status' => 'Pending']);
        Project::factory()->create(['status' => 'In Progress']);
        Project::factory()->create(['status' => 'Completed']);
        Project::factory()->create(['status' => 'On Hold']);

        $this->assertEquals(2, Project::active()->count());
        $this->assertEquals(1, Project::completed()->count());
    }

    /** @test */
    public function test_project_budget_tracking()
    {
        $this->actingAs($this->admin);

        $project = Project::factory()->create([
            'budget' => 10000
        ]);

        // Add expenses
        $project->expenses()->createMany([
            [
                'amount' => 3000,
                'date' => now(),
                'type' => 'Material',
                'description' => 'First expense'
            ],
            [
                'amount' => 4000,
                'date' => now(),
                'type' => 'Labor',
                'description' => 'Second expense'
            ]
        ]);

        $this->assertEquals(7000, $project->total_expenses);
        $this->assertEquals(3000, $project->remaining_budget);
    }

    /** @test */
    public function test_project_authorization()
    {
        // Test project creation permissions
        $this->actingAs($this->admin)
             ->post(route('projects.store'), [])
             ->assertStatus(302);

        $this->actingAs($this->manager)
             ->post(route('projects.store'), [])
             ->assertStatus(302);

        // A non-manager/admin user should be forbidden
        $regularUser = User::factory()->create(['role' => 'employee']);
        $this->actingAs($regularUser)
             ->post(route('projects.store'), [])
             ->assertForbidden();
    }
}
