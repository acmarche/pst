<?php

declare(strict_types=1);

use App\Enums\ActionScopeEnum;
use App\Enums\DepartmentEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\OperationalObjective\Pages\CreateOperationalObjective;
use App\Filament\Resources\OperationalObjective\Pages\EditOperationalObjective;
use App\Filament\Resources\OperationalObjective\Pages\ListOperationalObjectives;
use App\Filament\Resources\OperationalObjective\Pages\ViewOperationalObjective;
use App\Filament\Resources\OperationalObjective\RelationManagers\ActionsRelationManager;
use App\Models\Action;
use App\Models\OperationalObjective;
use App\Models\Role;
use App\Models\StrategicObjective;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    $adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);

    $this->strategicObjective = StrategicObjective::factory()->create();
});

describe('page rendering', function () {
    it('can render the index page', function () {
        Livewire::test(ListOperationalObjectives::class)
            ->assertOk();
    });

    it('can render the create page', function () {
        Livewire::test(CreateOperationalObjective::class)
            ->assertOk();
    });

    it('can render the view page', function () {
        $record = OperationalObjective::factory()->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);

        Livewire::test(ViewOperationalObjective::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function () {
        $record = OperationalObjective::factory()->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);

        Livewire::test(EditOperationalObjective::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('table columns', function () {
    it('has column', function (string $column) {
        Livewire::test(ListOperationalObjectives::class)
            ->assertTableColumnExists($column);
    })->with(['position', 'name', 'actions_count', 'department', 'created_at', 'updated_at']);

    it('can render column', function (string $column) {
        OperationalObjective::factory()->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);

        Livewire::test(ListOperationalObjectives::class)
            ->loadTable()
            ->assertCanRenderTableColumn($column);
    })->with(['position', 'name', 'actions_count']);

    it('can sort by name', function () {
        $records = OperationalObjective::factory(3)->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);

        Livewire::test(ListOperationalObjectives::class)
            ->loadTable()
            ->sortTable('name')
            ->assertCanSeeTableRecords($records->sortBy('name'), inOrder: true);
    });

    it('can search by name', function () {
        $records = OperationalObjective::factory(3)->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);
        $searchRecord = $records->first();

        Livewire::test(ListOperationalObjectives::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});

describe('crud operations', function () {
    it('can create an operational objective', function () {
        $newData = OperationalObjective::factory()->make();

        Livewire::test(CreateOperationalObjective::class)
            ->fillForm([
                'name' => $newData->name,
                'strategic_objective_id' => $this->strategicObjective->id,
                'department' => DepartmentEnum::VILLE,
                'scope' => ActionScopeEnum::INTERNAL,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(OperationalObjective::class, [
            'name' => $newData->name,
            'scope' => ActionScopeEnum::INTERNAL,
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);
    });

    it('can update an operational objective', function () {
        $record = OperationalObjective::factory()->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);
        $newData = OperationalObjective::factory()->make();

        Livewire::test(EditOperationalObjective::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(OperationalObjective::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });

    it('can delete an operational objective', function () {
        $record = OperationalObjective::factory()->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);

        Livewire::test(ViewOperationalObjective::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseMissing($record);
    });

    it('can bulk delete operational objectives', function () {
        $records = OperationalObjective::factory(3)->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);

        Livewire::test(ListOperationalObjectives::class)
            ->loadTable()
            ->assertCanSeeTableRecords($records)
            ->selectTableRecords($records)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified()
            ->assertCanNotSeeTableRecords($records);

        $records->each(fn (OperationalObjective $record) => assertDatabaseMissing($record));
    });
});

describe('form validation', function () {
    it('validates the form data on create', function (array $data, array $errors) {
        $newData = OperationalObjective::factory()->make();

        Livewire::test(CreateOperationalObjective::class)
            ->fillForm([
                'name' => $newData->name,
                'strategic_objective_id' => $this->strategicObjective->id,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        '`strategic_objective_id` is required' => [['strategic_objective_id' => null], ['strategic_objective_id' => 'required']],
    ]);

    it('validates the form data on edit', function (array $data, array $errors) {
        $record = OperationalObjective::factory()->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);

        Livewire::test(EditOperationalObjective::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $record->name,
                ...$data,
            ])
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);
});

describe('form fields', function () {
    it('has name field', function () {
        Livewire::test(CreateOperationalObjective::class)
            ->assertFormFieldExists('name');
    });

    it('has strategic_objective_id field', function () {
        Livewire::test(CreateOperationalObjective::class)
            ->assertFormFieldExists('strategic_objective_id');
    });
});

describe('relation manager', function () {
    it('can render the ActionsRelationManager', function () {
        $record = OperationalObjective::factory()->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);
        Action::factory(3)->create([
            'operational_objective_id' => $record->id,
        ]);

        Livewire::test(ActionsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => ViewOperationalObjective::class,
        ])
            ->assertOk();
    });

    it('can list actions in relation manager', function () {
        $record = OperationalObjective::factory()->create([
            'strategic_objective_id' => $this->strategicObjective->id,
        ]);
        $actions = Action::factory(3)->create([
            'operational_objective_id' => $record->id,
        ]);

        Livewire::test(ActionsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => ViewOperationalObjective::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($actions);
    });
});
