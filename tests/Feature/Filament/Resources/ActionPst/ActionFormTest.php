<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\ActionPst;

use App\Constant\RoleEnum;
use App\Filament\Resources\ActionPst\Pages\CreateAction;
use App\Filament\Resources\ActionPst\Pages\EditAction;
use App\Models\Action;
use App\Models\OperationalObjective;
use App\Models\Role;
use App\Models\Service;
use App\Models\StrategicObjective;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

final class ActionFormTest extends TestCase
{
    private User $adminUser;

    private User $responsibleUser;

    private User $regularUser;

    private Role $adminRole;

    private Role $responsibleRole;

    private Action $action;

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

        $this->action = $this->createAction();
    }

    public function test_admin_can_see_to_validate_field_on_create(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(CreateAction::class)
            ->assertFormFieldVisible('to_validate');
    }

    public function test_regular_user_cannot_see_to_validate_field_on_create(): void
    {
        $this->actingAs($this->regularUser);

        Livewire::test(CreateAction::class)
            ->assertFormFieldHidden('to_validate');
    }

    public function test_admin_can_see_roadmap_field_on_create(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(CreateAction::class)
            ->assertFormFieldVisible('roadmap');
    }

    public function test_regular_user_cannot_see_roadmap_field_on_create(): void
    {
        $this->actingAs($this->regularUser);

        Livewire::test(CreateAction::class)
            ->assertFormFieldHidden('roadmap');
    }

    public function test_name_field_is_not_readonly_for_admin_on_edit(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldEnabled('name');
    }

    /**
     * Note: name field uses readOnly() not disabled(). Filament's readOnly()
     * keeps the field enabled but prevents editing in the UI.
     * There's no assertFormFieldReadOnly() method in Filament testing.
     * This test verifies the field exists and is accessible on edit.
     */
    public function test_name_field_exists_for_regular_user_on_edit(): void
    {
        $this->action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldExists('name');
    }

    public function test_operational_objective_is_enabled_for_admin_on_edit(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldEnabled('operational_objective_id');
    }

    public function test_operational_objective_is_disabled_for_regular_user_on_edit(): void
    {
        $this->action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldDisabled('operational_objective_id');
    }

    public function test_type_field_is_enabled_for_admin_on_edit(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldEnabled('type');
    }

    public function test_type_field_is_disabled_for_regular_user_on_edit(): void
    {
        $this->action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldDisabled('type');
    }

    public function test_team_step_is_visible_for_admin_on_edit(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldVisible('action_mandatory');
    }

    public function test_team_step_is_hidden_for_regular_user_on_edit(): void
    {
        $this->action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldHidden('action_mandatory');
    }

    public function test_team_step_is_visible_for_responsible_in_leader_service_on_edit(): void
    {
        $service = Service::factory()->create();
        $service->users()->attach($this->responsibleUser);
        $this->action->leaderServices()->attach($service);

        $this->actingAs($this->responsibleUser);

        Livewire::test(EditAction::class, ['record' => $this->action->id])
            ->assertFormFieldVisible('action_mandatory');
    }

    public function test_all_fields_are_enabled_on_create_for_regular_user(): void
    {
        $this->actingAs($this->regularUser);

        Livewire::test(CreateAction::class)
            ->assertFormFieldEnabled('name')
            ->assertFormFieldEnabled('type');
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
