<?php

declare(strict_types=1);

use App\Enums\RoleEnum;
use App\Filament\Resources\Service\Pages\CreateService;
use App\Filament\Resources\Service\Pages\EditService;
use App\Filament\Resources\Service\Pages\ListServices;
use App\Filament\Resources\Service\Pages\ViewService;
use App\Models\Role;
use App\Models\Service;
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
        Livewire::test(ListServices::class)
            ->assertOk();
    });

    it('can render the create page', function () {
        Livewire::test(CreateService::class)
            ->assertOk();
    });

    it('can render the view page', function () {
        $record = Service::factory()->create();

        Livewire::test(ViewService::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function () {
        $record = Service::factory()->create();

        Livewire::test(EditService::class, [
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
        Livewire::test(ListServices::class)
            ->assertTableColumnExists($column);
    })->with(['name', 'initials', 'users_count']);

    it('can render column', function (string $column) {
        Service::factory()->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->assertCanRenderTableColumn($column);
    })->with(['name', 'users_count']);

    it('can sort by name', function () {
        $records = Service::factory(3)->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->sortTable('name')
            ->assertCanSeeTableRecords($records->sortBy('name'), inOrder: true);
    });

    it('can search by name', function () {
        $records = Service::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});

describe('crud operations', function () {
    it('can create a service', function () {
        $newData = Service::factory()->make();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $newData->name,
                'initials' => 'TST',
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(Service::class, [
            'name' => $newData->name,
            'initials' => 'TST',
        ]);
    });

    it('can create a service with users', function () {
        $newData = Service::factory()->make();
        $users = User::factory(2)->create();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $newData->name,
                'users' => $users->pluck('id')->toArray(),
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        $service = Service::where('name', $newData->name)->first();
        expect($service->users)->toHaveCount(2);
    });

    it('can update a service', function () {
        $record = Service::factory()->create();
        $newData = Service::factory()->make();

        Livewire::test(EditService::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Service::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });

    it('can delete a service', function () {
        $record = Service::factory()->create();

        Livewire::test(ViewService::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseMissing($record);
    });

    it('can bulk delete services', function () {
        $records = Service::factory(3)->create();

        Livewire::test(ListServices::class)
            ->loadTable()
            ->assertCanSeeTableRecords($records)
            ->selectTableRecords($records)
            ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk())
            ->assertNotified()
            ->assertCanNotSeeTableRecords($records);

        $records->each(fn (Service $record) => assertDatabaseMissing($record));
    });
});

describe('form validation', function () {
    it('validates the form data on create', function (array $data, array $errors) {
        $newData = Service::factory()->make();

        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        '`initials` is max 30 characters' => [['initials' => Str::random(31)], ['initials' => 'max']],
    ]);

    it('validates the form data on edit', function (array $data, array $errors) {
        $record = Service::factory()->create();

        Livewire::test(EditService::class, [
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
        Livewire::test(CreateService::class)
            ->assertFormFieldExists('name');
    });

    it('has initials field', function () {
        Livewire::test(CreateService::class)
            ->assertFormFieldExists('initials');
    });

    it('has users field', function () {
        Livewire::test(CreateService::class)
            ->assertFormFieldExists('users');
    });
});
