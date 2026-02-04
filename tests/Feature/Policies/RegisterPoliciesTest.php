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

    private User $regularUser;

    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole);
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
