<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\ActionPst;

use App\Enums\RoleEnum;
use App\Filament\Resources\ActionPst\Pages\ListActions;
use App\Models\Action;
use App\Models\OperationalObjective;
use App\Models\Role;
use App\Models\StrategicObjective;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class ActionTablesTest extends TestCase
{
    private User $adminUser;

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole);
    }

    public function test_can_render_list_actions_page(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActions::class)
            ->assertOk();
    }

    public function test_is_internal_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActions::class)
            ->assertTableFilterExists('isInternal');
    }

    public function test_can_filter_actions_by_is_internal_true(): void
    {
        $this->actingAs($this->adminUser);

        $internalStrategicObjective = StrategicObjective::factory()->create([
            'is_internal' => true,
            'department' => 'VILLE',
        ]);
        $externalStrategicObjective = StrategicObjective::factory()->create([
            'is_internal' => false,
            'department' => 'VILLE',
        ]);

        $internalOperationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $internalStrategicObjective->id,
        ]);
        $externalOperationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $externalStrategicObjective->id,
        ]);

        $internalActions = Action::factory()->count(3)->create([
            'operational_objective_id' => $internalOperationalObjective->id,
            'department' => 'VILLE',
            'to_validate' => false,
        ]);
        $externalActions = Action::factory()->count(2)->create([
            'operational_objective_id' => $externalOperationalObjective->id,
            'department' => 'VILLE',
            'to_validate' => false,
        ]);

        Livewire::test(ListActions::class)
            ->loadTable()
            ->filterTable('isInternal', '1')
            ->assertCanSeeTableRecords($internalActions)
            ->assertCanNotSeeTableRecords($externalActions);
    }

    public function test_can_filter_actions_by_is_internal_false(): void
    {
        $this->actingAs($this->adminUser);

        $internalStrategicObjective = StrategicObjective::factory()->create([
            'is_internal' => true,
            'department' => 'VILLE',
        ]);
        $externalStrategicObjective = StrategicObjective::factory()->create([
            'is_internal' => false,
            'department' => 'VILLE',
        ]);

        $internalOperationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $internalStrategicObjective->id,
        ]);
        $externalOperationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $externalStrategicObjective->id,
        ]);

        $internalActions = Action::factory()->count(3)->create([
            'operational_objective_id' => $internalOperationalObjective->id,
            'department' => 'VILLE',
            'to_validate' => false,
        ]);
        $externalActions = Action::factory()->count(2)->create([
            'operational_objective_id' => $externalOperationalObjective->id,
            'department' => 'VILLE',
            'to_validate' => false,
        ]);

        Livewire::test(ListActions::class)
            ->loadTable()
            ->filterTable('isInternal', '0')
            ->assertCanSeeTableRecords($externalActions)
            ->assertCanNotSeeTableRecords($internalActions);
    }

    public function test_state_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActions::class)
            ->assertTableFilterExists('state');
    }

    public function test_type_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActions::class)
            ->assertTableFilterExists('type');
    }

    public function test_department_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActions::class)
            ->assertTableFilterExists('department');
    }

    public function test_operational_objectives_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActions::class)
            ->assertTableFilterExists('operational_objectives');
    }

    public function test_users_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActions::class)
            ->assertTableFilterExists('users');
    }

    public function test_services_filter_exists(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(ListActions::class)
            ->assertTableFilterExists('services');
    }
}
