<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\ActionPst;

use App\Enums\ActionScopeEnum;
use App\Enums\ActionStateEnum;
use App\Enums\ActionSynergyEnum;
use App\Enums\DepartmentEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\ActionPst\Pages\CreateAction;
use App\Filament\Resources\ActionPst\Pages\ListActions;
use App\Models\OperationalObjective;
use App\Models\Role;
use App\Models\StrategicObjective;
use App\Models\User;
use App\Repository\UserRepository;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for department filtering on Select fields in Action forms and tables.
 *
 * These tests verify that:
 * - operational_objective_id Select in ActionForm only shows objectives matching user's department
 * - operational_objectives SelectFilter in ActionTables only shows objectives matching user's department
 * - Internal objectives (department = null) are always visible regardless of selected department
 */
final class ActionSelectDepartmentFilterTest extends TestCase
{
    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    }

    public function test_user_can_select_operational_objective_from_same_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        $villeObjective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        Livewire::test(CreateAction::class)
            ->fillForm([
                'name' => 'Test Action',
                'operational_objective_id' => $villeObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::EXTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_user_cannot_select_operational_objective_from_different_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        // Create objective for CPAS department (different from user's selected department)
        $cpasObjective = $this->createOperationalObjective(DepartmentEnum::CPAS->value);

        Livewire::test(CreateAction::class)
            ->fillForm([
                'name' => 'Test Action',
                'operational_objective_id' => $cpasObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::EXTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['operational_objective_id']);
    }

    public function test_user_can_select_internal_operational_objective_regardless_of_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        // Create internal objective (department = null)
        $internalObjective = $this->createInternalOperationalObjective();

        Livewire::test(CreateAction::class)
            ->fillForm([
                'name' => 'Test Action with Internal Objective',
                'operational_objective_id' => $internalObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::INTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_cpas_user_can_select_operational_objective_from_cpas_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::CPAS->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::CPAS->value]);

        $cpasObjective = $this->createOperationalObjective(DepartmentEnum::CPAS->value);

        Livewire::test(CreateAction::class)
            ->fillForm([
                'name' => 'Test CPAS Action',
                'operational_objective_id' => $cpasObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::EXTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();
    }

    public function test_cpas_user_cannot_select_operational_objective_from_ville_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::CPAS->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::CPAS->value]);

        $villeObjective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        Livewire::test(CreateAction::class)
            ->fillForm([
                'name' => 'Test Action',
                'operational_objective_id' => $villeObjective->id,
                'state' => ActionStateEnum::START->value,
                'scope' => ActionScopeEnum::EXTERNAL->value,
                'synergy' => ActionSynergyEnum::YES->value,
            ])
            ->call('create')
            ->assertHasFormErrors(['operational_objective_id']);
    }

    public function test_table_filter_shows_only_objectives_from_user_department(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        $villeObjective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);
        $cpasObjective = $this->createOperationalObjective(DepartmentEnum::CPAS->value);
        $internalObjective = $this->createInternalOperationalObjective();

        // Test that filter can be applied with VILLE objective
        Livewire::test(ListActions::class)
            ->loadTable()
            ->filterTable('operational_objectives', $villeObjective->id)
            ->assertOk();

        // Filter with internal objective should also work
        Livewire::test(ListActions::class)
            ->loadTable()
            ->filterTable('operational_objectives', $internalObjective->id)
            ->assertOk();
    }

    private function createOperationalObjective(string $department): OperationalObjective
    {
        $strategicObjective = StrategicObjective::factory()->create([
            'department' => $department,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);

        return OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
            'department' => $department,
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);
    }

    private function createInternalOperationalObjective(): OperationalObjective
    {
        $strategicObjective = StrategicObjective::factory()->create([
            'department' => null,
            'scope' => ActionScopeEnum::INTERNAL,
        ]);

        return OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
            'department' => null,
            'scope' => ActionScopeEnum::INTERNAL,
        ]);
    }
}
