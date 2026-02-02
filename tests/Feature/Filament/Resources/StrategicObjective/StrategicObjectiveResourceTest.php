<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Filament\Resources\StrategicObjective\Pages\CreateStrategicObjective;
use App\Filament\Resources\StrategicObjective\Pages\EditStrategicObjective;
use App\Filament\Resources\StrategicObjective\Pages\ListStrategicObjectives;
use App\Filament\Resources\StrategicObjective\Pages\ViewStrategicObjective;
use App\Filament\Resources\StrategicObjective\RelationManagers\OosRelationManager;
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
});

describe('page rendering', function () {
    it('can render the index page', function () {
        Livewire::test(ListStrategicObjectives::class)
            ->assertOk();
    });

    it('can render the create page', function () {
        Livewire::test(CreateStrategicObjective::class)
            ->assertOk();
    });

    it('can render the view page', function () {
        $record = StrategicObjective::factory()->create();

        Livewire::test(ViewStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function () {
        $record = StrategicObjective::factory()->create();

        Livewire::test(EditStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
                'position' => $record->position,
            ]);
    });
});

describe('table columns', function () {
    it('has column', function (string $column) {
        Livewire::test(ListStrategicObjectives::class)
            ->assertTableColumnExists($column);
    })->with(['position', 'name', 'oos_count', 'department', 'created_at']);

    it('can render column', function (string $column) {
        StrategicObjective::factory()->create();

        Livewire::test(ListStrategicObjectives::class)
            ->loadTable()
            ->assertCanRenderTableColumn($column);
    })->with(['position', 'name', 'oos_count']);

    it('can sort by position', function () {
        $records = StrategicObjective::factory(3)->create();

        Livewire::test(ListStrategicObjectives::class)
            ->loadTable()
            ->sortTable('position')
            ->assertCanSeeTableRecords($records->sortBy('position'), inOrder: true)
            ->sortTable('position', 'desc')
            ->assertCanSeeTableRecords($records->sortByDesc('position'), inOrder: true);
    });

    it('can sort by name', function () {
        $records = StrategicObjective::factory(3)->create();

        Livewire::test(ListStrategicObjectives::class)
            ->loadTable()
            ->sortTable('name')
            ->assertCanSeeTableRecords($records->sortBy('name'), inOrder: true);
    });

    it('can search by name', function () {
        $records = StrategicObjective::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListStrategicObjectives::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});

describe('crud operations', function () {
    it('can create a strategic objective', function () {
        $newData = StrategicObjective::factory()->make();

        Livewire::test(CreateStrategicObjective::class)
            ->fillForm([
                'name' => $newData->name,
                'department' => $newData->department,
                'scope' => $newData->scope,
                'position' => $newData->position,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(StrategicObjective::class, [
            'name' => $newData->name,
            'position' => $newData->position,
        ]);
    });

    it('can update a strategic objective', function () {
        $record = StrategicObjective::factory()->create();
        $newData = StrategicObjective::factory()->make();

        Livewire::test(EditStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
                'position' => $newData->position,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(StrategicObjective::class, [
            'id' => $record->id,
            'name' => $newData->name,
            'position' => $newData->position,
        ]);
    });

    it('can delete a strategic objective', function () {
        $record = StrategicObjective::factory()->create();

        Livewire::test(EditStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseMissing($record);
    });

    it('can bulk delete strategic objectives', function () {
        $records = StrategicObjective::factory(3)->create();

        Livewire::test(ListStrategicObjectives::class)
            ->loadTable()
            ->assertCanSeeTableRecords($records)
            ->selectTableRecords($records)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified()
            ->assertCanNotSeeTableRecords($records);

        $records->each(fn (StrategicObjective $record) => assertDatabaseMissing($record));
    });
});

describe('form validation', function () {
    it('validates the form data on create', function (array $data, array $errors) {
        $newData = StrategicObjective::factory()->make();

        Livewire::test(CreateStrategicObjective::class)
            ->fillForm([
                'name' => $newData->name,
                'department' => $newData->department,
                'position' => $newData->position,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        '`position` is required' => [['position' => null], ['position' => 'required']],
        '`position` must be numeric' => [['position' => 'abc'], ['position' => 'numeric']],
    ]);

    it('validates the form data on edit', function (array $data, array $errors) {
        $record = StrategicObjective::factory()->create();

        Livewire::test(EditStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $record->name,
                'position' => $record->position,
                ...$data,
            ])
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        '`position` is required' => [['position' => null], ['position' => 'required']],
    ]);
});

describe('form fields', function () {
    it('has department field visible on create', function () {
        Livewire::test(CreateStrategicObjective::class)
            ->assertFormFieldVisible('department');
    });

    it('has department field hidden on edit', function () {
        $record = StrategicObjective::factory()->create();

        Livewire::test(EditStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->assertFormFieldHidden('department');
    });

    it('has scope field', function () {
        Livewire::test(CreateStrategicObjective::class)
            ->assertFormFieldExists('scope');
    });
});

describe('relation manager', function () {
    it('can render the OosRelationManager', function () {
        $record = StrategicObjective::factory()->create();
        OperationalObjective::factory(3)->create([
            'strategic_objective_id' => $record->id,
        ]);

        Livewire::test(OosRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => ViewStrategicObjective::class,
        ])
            ->assertOk();
    });

    it('can list operational objectives in relation manager', function () {
        $record = StrategicObjective::factory()->create();
        $oos = OperationalObjective::factory(3)->create([
            'strategic_objective_id' => $record->id,
        ]);

        Livewire::test(OosRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => ViewStrategicObjective::class,
        ])
            ->assertCanSeeTableRecords($oos);
    });
});
