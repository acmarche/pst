<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Enums\RoleEnum;
use App\Models\Action;
use App\Models\OperationalObjective;
use App\Models\Role;
use App\Models\Service;
use App\Models\StrategicObjective;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class RegisterPoliciesTest extends TestCase
{
    private User $adminUser;

    private User $responsibleUser;

    private User $regularUser;

    private Role $adminRole;

    private Role $responsibleRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
        $this->responsibleRole = Role::factory()->create(['name' => RoleEnum::RESPONSIBLE->value]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole);

        $this->responsibleUser = User::factory()->create();
        $this->responsibleUser->roles()->attach($this->responsibleRole);

        $this->regularUser = User::factory()->create();
    }

    public function test_teams_edit_gate_allows_create_operation_for_any_user(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->regularUser);

        $result = Gate::check('teams-edit', [$action, 'create']);

        $this->assertTrue($result);
    }

    public function test_teams_edit_gate_allows_admin_on_edit_operation(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->adminUser);

        $result = Gate::check('teams-edit', [$action, 'edit']);

        $this->assertTrue($result);
    }

    public function test_teams_edit_gate_denies_regular_user_on_edit_operation(): void
    {
        $action = $this->createAction();

        $this->actingAs($this->regularUser);

        $result = Gate::check('teams-edit', [$action, 'edit']);

        $this->assertFalse($result);
    }

    public function test_teams_edit_gate_allows_responsible_when_user_is_in_leader_service(): void
    {
        $service = Service::factory()->create();
        $service->users()->attach($this->responsibleUser);

        $action = $this->createAction();
        $action->leaderServices()->attach($service);

        $this->actingAs($this->responsibleUser);

        $result = Gate::check('teams-edit', [$action, 'edit']);

        $this->assertTrue($result);
    }

    public function test_teams_edit_gate_denies_responsible_when_user_is_not_in_leader_service(): void
    {
        $service = Service::factory()->create();
        $action = $this->createAction();
        $action->leaderServices()->attach($service);

        $this->actingAs($this->responsibleUser);

        $result = Gate::check('teams-edit', [$action, 'edit']);

        $this->assertFalse($result);
    }

    public function test_teams_edit_gate_denies_responsible_when_user_is_only_in_partner_service(): void
    {
        $leaderService = Service::factory()->create();
        $partnerService = Service::factory()->create();
        $partnerService->users()->attach($this->responsibleUser);

        $action = $this->createAction();
        $action->leaderServices()->attach($leaderService);
        $action->partnerServices()->attach($partnerService);

        $this->actingAs($this->responsibleUser);

        $result = Gate::check('teams-edit', [$action, 'edit']);

        $this->assertFalse($result);
    }

    private function createAction(): Action
    {
        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        return Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
        ]);
    }
}
