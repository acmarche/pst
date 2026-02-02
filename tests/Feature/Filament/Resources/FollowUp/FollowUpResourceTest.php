<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Filament\Resources\ActionPst\Pages\ViewAction;
use App\Filament\Resources\ActionPst\RelationManagers\FollowUpsRelationManager;
use App\Filament\Resources\FollowUp\Pages\CreateFollowUp;
use App\Filament\Resources\FollowUp\Pages\EditFollowUp;
use App\Filament\Resources\FollowUp\Pages\ListFollowUps;
use App\Filament\Resources\FollowUp\Pages\ViewFollowUp;
use App\Models\Action;
use App\Models\FollowUp;
use App\Models\OperationalObjective;
use App\Models\Role;
use App\Models\StrategicObjective;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);

    $strategicObjective = StrategicObjective::factory()->create();
    $operationalObjective = OperationalObjective::factory()->create([
        'strategic_objective_id' => $strategicObjective->id,
    ]);
    $this->action = Action::factory()->create([
        'operational_objective_id' => $operationalObjective->id,
    ]);
});

describe('page rendering', function () {
    it('can render the index page', function () {
        Livewire::test(ListFollowUps::class)
            ->assertOk();
    });

    it('can render the create page', function () {
        Livewire::test(CreateFollowUp::class)
            ->assertOk();
    });

    it('can render the view page', function () {
        $record = FollowUp::factory()->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(ViewFollowUp::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function () {
        $record = FollowUp::factory()->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(EditFollowUp::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });
});

describe('relation manager', function () {
    it('can render the FollowUpsRelationManager', function () {
        FollowUp::factory(3)->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewAction::class,
        ])
            ->assertOk();
    });

    it('can list followups in relation manager', function () {
        $followups = FollowUp::factory(3)->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewAction::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($followups);
    });

    it('can create a followup through relation manager', function () {
        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewAction::class,
        ])
            ->callTableAction('create', data: [
                'content' => '<p>Test followup content</p>',
            ])
            ->assertNotified();

        assertDatabaseHas(FollowUp::class, [
            'action_id' => $this->action->id,
            'content' => '<p>Test followup content</p>',
        ]);
    });

    it('can edit a followup through relation manager', function () {
        $followup = FollowUp::factory()->create([
            'action_id' => $this->action->id,
            'content' => '<p>Original content</p>',
        ]);

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewAction::class,
        ])
            ->callTableAction('edit', $followup, data: [
                'content' => '<p>Updated content</p>',
            ])
            ->assertNotified();

        assertDatabaseHas(FollowUp::class, [
            'id' => $followup->id,
            'content' => '<p>Updated content</p>',
        ]);
    });

    it('can delete a followup through relation manager', function () {
        $followup = FollowUp::factory()->create([
            'action_id' => $this->action->id,
        ]);

        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewAction::class,
        ])
            ->callTableAction('delete', $followup)
            ->assertNotified();

        assertDatabaseMissing($followup);
    });

    it('validates content is required in relation manager', function () {
        Livewire::test(FollowUpsRelationManager::class, [
            'ownerRecord' => $this->action,
            'pageClass' => ViewAction::class,
        ])
            ->callTableAction('create', data: [
                'content' => null,
            ])
            ->assertHasTableActionErrors(['content' => 'required']);
    });
});

describe('form fields', function () {
    it('has name field', function () {
        Livewire::test(CreateFollowUp::class)
            ->assertFormFieldExists('name');
    });

    it('has icon field', function () {
        Livewire::test(CreateFollowUp::class)
            ->assertFormFieldExists('icon');
    });
});
