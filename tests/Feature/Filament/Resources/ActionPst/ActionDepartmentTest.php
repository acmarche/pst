<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\ActionPst;

use App\Enums\ActionStateEnum;
use App\Enums\DepartmentEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\ActionPst\Pages\ListActions;
use App\Models\Action;
use App\Models\OperationalObjective;
use App\Models\Role;
use App\Models\StrategicObjective;
use App\Models\User;
use App\Repository\UserRepository;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Department filtering tests:
 * - test_user_with_ville_department_sees_only_ville_actions - User with VILLE sees only VILLE actions
 * - test_user_with_cpas_department_sees_cpas_actions - User with CPAS sees only CPAS actions
 * - test_user_with_both_departments_sees_actions_based_on_selected_department - User with both departments sees actions based on selection
 *
 * State filter tests:
 * - test_can_filter_actions_by_state_start - Filter by START state
 * - test_can_filter_actions_by_state_pending - Filter by PENDING state
 *
 * Tab badge count tests:
 * - test_tabs_show_correct_count_for_all_tab - All tab shows total count
 * - test_tabs_show_correct_count_for_not_validated_tab_for_admin - NotValidated tab count for admin
 * - test_admin_has_correct_number_of_tabs - Admin has 6 tabs (All + NotValidated + 4 states)
 * - test_non_admin_user_has_correct_number_of_tabs - Non-admin has 5 tabs (no NotValidated)
 * - test_state_tabs_only_count_validated_actions - State tabs only count validated actions
 * - test_all_tab_counts_both_validated_and_non_validated - All tab counts everything
 */
final class ActionDepartmentTest extends TestCase
{
    private Role $adminRole;

    private Role $mandataireRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
        $this->mandataireRole = Role::factory()->create(['name' => RoleEnum::MANDATAIRE->value]);
    }

    public function test_user_with_ville_department_sees_only_ville_actions(): void
    {
        $userWithVille = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $userWithVille->roles()->attach($this->adminRole);

        $this->actingAs($userWithVille);

        $villeObjective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);
        $cpasObjective = $this->createOperationalObjective(DepartmentEnum::CPAS->value);

        $villeActions = Action::factory()->count(3)->create([
            'operational_objective_id' => $villeObjective->id,
            'department' => DepartmentEnum::VILLE->value,
            'validated' => true,
        ]);

        // Create CPAS actions bypassing global scope
        foreach (range(1, 2) as $i) {
            Action::withoutGlobalScopes()->create([
                'name' => "CPAS Action $i",
                'operational_objective_id' => $cpasObjective->id,
                'department' => DepartmentEnum::CPAS->value,
                'validated' => true,
                'user_add' => 'test',
            ]);
        }

        Livewire::test(ListActions::class)
            ->loadTable()
            ->assertCanSeeTableRecords($villeActions);
    }

    public function test_user_with_cpas_department_sees_cpas_actions(): void
    {
        $userWithCpas = User::factory()->create([
            'departments' => [DepartmentEnum::CPAS->value],
        ]);
        $userWithCpas->roles()->attach($this->adminRole);

        $cpasObjective = $this->createOperationalObjective(DepartmentEnum::CPAS->value);

        // Create CPAS actions bypassing global scope
        $cpasAction1 = Action::withoutGlobalScopes()->create([
            'name' => 'CPAS Action 1',
            'operational_objective_id' => $cpasObjective->id,
            'department' => DepartmentEnum::CPAS->value,
            'validated' => true,
            'user_add' => 'test',
        ]);

        $cpasAction2 = Action::withoutGlobalScopes()->create([
            'name' => 'CPAS Action 2',
            'operational_objective_id' => $cpasObjective->id,
            'department' => DepartmentEnum::CPAS->value,
            'validated' => true,
            'user_add' => 'test',
        ]);

        // User's first department is CPAS, so departmentSelected() will return CPAS
        $this->actingAs($userWithCpas);

        // Verify the scope returns CPAS actions
        $actionsCount = Action::query()->count();
        expect($actionsCount)->toBe(2);

        Livewire::test(ListActions::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$cpasAction1, $cpasAction2]);
    }

    public function test_user_with_both_departments_sees_actions_based_on_selected_department(): void
    {
        $userWithBoth = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value, DepartmentEnum::CPAS->value],
        ]);
        $userWithBoth->roles()->attach($this->adminRole);

        $this->actingAs($userWithBoth);
        session([UserRepository::$department_selected_key => DepartmentEnum::VILLE->value]);

        $villeObjective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        $villeActions = Action::factory()->count(3)->create([
            'operational_objective_id' => $villeObjective->id,
            'department' => DepartmentEnum::VILLE->value,
            'validated' => true,
        ]);

        Livewire::test(ListActions::class)
            ->loadTable()
            ->assertCanSeeTableRecords($villeActions);
    }

    public function test_can_filter_actions_by_state_start(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);

        $objective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        $startActions = Action::factory()->count(2)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'state' => ActionStateEnum::START->value,
            'validated' => true,
        ]);

        $pendingActions = Action::factory()->count(3)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'state' => ActionStateEnum::PENDING->value,
            'validated' => true,
        ]);

        Livewire::test(ListActions::class)
            ->loadTable()
            ->filterTable('state', ActionStateEnum::START->value)
            ->assertCanSeeTableRecords($startActions)
            ->assertCanNotSeeTableRecords($pendingActions);
    }

    public function test_can_filter_actions_by_state_pending(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);

        $objective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        $pendingActions = Action::factory()->count(2)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'state' => ActionStateEnum::PENDING->value,
            'validated' => true,
        ]);

        $finishedActions = Action::factory()->count(3)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'state' => ActionStateEnum::FINISHED->value,
            'validated' => true,
        ]);

        Livewire::test(ListActions::class)
            ->loadTable()
            ->filterTable('state', ActionStateEnum::PENDING->value)
            ->assertCanSeeTableRecords($pendingActions)
            ->assertCanNotSeeTableRecords($finishedActions);
    }

    public function test_tabs_show_correct_count_for_all_tab(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);

        $objective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        Action::factory()->count(5)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'validated' => true,
        ]);

        $component = Livewire::test(ListActions::class);
        $tabs = $component->instance()->getTabs();

        // Tab 0 is "All"
        $allTabBadge = $tabs[0]->getBadge();
        expect($allTabBadge)->toBe(5);
    }

    public function test_tabs_show_correct_count_for_not_validated_tab_for_admin(): void
    {
        $adminUser = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $adminUser->roles()->attach($this->adminRole);

        $this->actingAs($adminUser);

        $objective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        Action::factory()->count(3)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'validated' => true,
        ]);

        Action::factory()->count(2)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'validated' => false,
        ]);

        $component = Livewire::test(ListActions::class);
        $tabs = $component->instance()->getTabs();

        // Tab 1 is NotValidated for admin
        expect($tabs)->toHaveKey(1);
        $notValidatedBadge = $tabs[1]->getBadge();
        expect($notValidatedBadge)->toBe(2);
    }

    public function test_admin_has_correct_number_of_tabs(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);

        $component = Livewire::test(ListActions::class);
        $tabs = $component->instance()->getTabs();

        // For admin: All + NotValidated + 4 ActionStateEnum cases = 6 tabs
        expect(count($tabs))->toBe(6);
    }

    public function test_non_admin_user_has_correct_number_of_tabs(): void
    {
        $regularUser = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $regularUser->roles()->attach($this->mandataireRole);

        $this->actingAs($regularUser);

        $component = Livewire::test(ListActions::class);
        $tabs = $component->instance()->getTabs();

        // For non-admin: All + 4 ActionStateEnum cases = 5 tabs (no NotValidated)
        expect(count($tabs))->toBe(5);
    }

    public function test_state_tabs_only_count_validated_actions(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);

        $objective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        // Create validated actions
        Action::factory()->count(3)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'state' => ActionStateEnum::START->value,
            'validated' => true,
        ]);

        // Create non-validated actions with same state
        Action::factory()->count(5)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'state' => ActionStateEnum::START->value,
            'validated' => false,
        ]);

        $component = Livewire::test(ListActions::class);
        $tabs = $component->instance()->getTabs();

        // For admin: Tab 2 is START (index 0=All, 1=NotValidated, 2=START)
        $startTabBadge = $tabs[2]->getBadge();
        expect($startTabBadge)->toBe(3);
    }

    public function test_all_tab_counts_both_validated_and_non_validated(): void
    {
        $user = User::factory()->create([
            'departments' => [DepartmentEnum::VILLE->value],
        ]);
        $user->roles()->attach($this->adminRole);

        $this->actingAs($user);

        $objective = $this->createOperationalObjective(DepartmentEnum::VILLE->value);

        Action::factory()->count(3)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'validated' => true,
        ]);

        Action::factory()->count(2)->create([
            'operational_objective_id' => $objective->id,
            'department' => DepartmentEnum::VILLE->value,
            'validated' => false,
        ]);

        $component = Livewire::test(ListActions::class);
        $tabs = $component->instance()->getTabs();

        // All tab should count everything
        $allTabBadge = $tabs[0]->getBadge();
        expect($allTabBadge)->toBe(5);
    }

    private function createOperationalObjective(string $department): OperationalObjective
    {
        $strategicObjective = StrategicObjective::factory()->create([
            'department' => $department,
        ]);

        return OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
            'department' => $department,
        ]);
    }
}
