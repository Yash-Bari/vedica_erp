<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $supervisor;
    protected $employee;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->employee = User::factory()->create(['role' => 'employee']);

        // Create a test project
        $this->project = Project::factory()->create([
            'manager_id' => $this->manager->id
        ]);
    }

    /** @test */
    public function admin_can_create_machine()
    {
        $this->actingAs($this->admin);

        $machineData = [
            'name' => 'Test Excavator',
            'type' => 'Excavator',
            'status' => 'Available',
            'project_id' => $this->project->id,
            'purchase_price' => 50000.00,
            'purchase_date' => now()->format('Y-m-d')
        ];

        $response = $this->post(route('machines.store'), $machineData);

        $response->assertRedirect();
        $this->assertDatabaseHas('machines', [
            'name' => 'Test Excavator',
            'type' => 'Excavator',
            'project_id' => $this->project->id
        ]);
    }

    /** @test */
    public function manager_can_create_machine_for_own_project()
    {
        $this->actingAs($this->manager);

        $machineData = [
            'name' => 'Project Bulldozer',
            'type' => 'Bulldozer',
            'status' => 'Available',
            'project_id' => $this->project->id
        ];

        $response = $this->post(route('machines.store'), $machineData);

        $response->assertRedirect();
        $this->assertDatabaseHas('machines', [
            'name' => 'Project Bulldozer',
            'project_id' => $this->project->id
        ]);
    }

    /** @test */
    public function employee_cannot_create_machine()
    {
        $this->actingAs($this->employee);

        $machineData = [
            'name' => 'Unauthorized Machine',
            'type' => 'Crane'
        ];

        $response = $this->post(route('machines.store'), $machineData);

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_update_machine()
    {
        $this->actingAs($this->admin);

        $machine = Machine::factory()->create();

        $updatedData = [
            'name' => 'Updated Machine Name',
            'type' => 'Loader',
            'status' => 'Maintenance',
            'project_id' => $this->project->id
        ];

        $response = $this->put(route('machines.update', $machine), $updatedData);

        $response->assertRedirect();
        $this->assertDatabaseHas('machines', [
            'id' => $machine->id,
            'name' => 'Updated Machine Name',
            'type' => 'Loader',
            'status' => 'Maintenance'
        ]);
    }

    /** @test */
    public function manager_can_update_machine_in_own_project()
    {
        $this->actingAs($this->manager);

        $machine = Machine::factory()->create([
            'project_id' => $this->project->id
        ]);

        $updatedData = [
            'name' => 'Updated Project Machine',
            'status' => 'Repair'
        ];

        $response = $this->put(route('machines.update', $machine), $updatedData);

        $response->assertRedirect();
        $this->assertDatabaseHas('machines', [
            'id' => $machine->id,
            'name' => 'Updated Project Machine',
            'status' => 'Repair'
        ]);
    }

    /** @test */
    public function manager_cannot_update_machine_in_other_project()
    {
        $this->actingAs($this->manager);

        $otherProject = Project::factory()->create();
        $machine = Machine::factory()->create([
            'project_id' => $otherProject->id
        ]);

        $updatedData = [
            'name' => 'Unauthorized Update'
        ];

        $response = $this->put(route('machines.update', $machine), $updatedData);

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_delete_machine()
    {
        $this->actingAs($this->admin);

        $machine = Machine::factory()->create();

        $response = $this->delete(route('machines.destroy', $machine));

        $response->assertRedirect();
        $this->assertSoftDeleted('machines', ['id' => $machine->id]);
    }

    /** @test */
    public function non_admin_cannot_delete_machine()
    {
        $this->actingAs($this->manager);

        $machine = Machine::factory()->create();

        $response = $this->delete(route('machines.destroy', $machine));

        $response->assertForbidden();
    }

    /** @test */
    public function can_generate_machine_maintenance_report()
    {
        $this->actingAs($this->admin);

        Machine::factory()->count(5)->create([
            'status' => 'Maintenance'
        ]);

        $response = $this->get(route('machines.maintenance'));

        $response->assertOk();
        $response->assertViewHas('machines');
    }

    /** @test */
    public function can_export_machines_to_csv()
    {
        $this->actingAs($this->admin);

        Machine::factory()->count(10)->create();

        $response = $this->get(route('machines.export'));

        $response->assertOk();
        $response->assertDownload();
    }
}
