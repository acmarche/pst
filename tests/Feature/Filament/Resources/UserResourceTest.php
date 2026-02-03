<?php

declare(strict_types=1);

use App\Enums\DepartmentEnum;
use App\Enums\RoleEnum;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);
});

it('can render the index page', function () {
    Livewire::test(ListUsers::class)
        ->assertOk();
});

it('can render the edit page', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->id,
    ])
        ->assertOk()
        ->assertSchemaStateSet([
            'departments' => $user->departments,
        ]);
});

it('has column', function (string $column) {
    Livewire::test(ListUsers::class)
        ->assertTableColumnExists($column);
})->with(['first_name', 'last_name', 'email', 'created_at']);

it('can render column', function (string $column) {
    Livewire::test(ListUsers::class)
        ->loadTable()
        ->assertCanRenderTableColumn($column);
})->with(['first_name', 'last_name', 'email']);

it('can sort column', function (string $column) {
    $records = User::factory(5)->create();

    Livewire::test(ListUsers::class)
        ->loadTable()
        ->sortTable($column)
        ->assertCanSeeTableRecords($records->sortBy($column), inOrder: true)
        ->sortTable($column, 'desc')
        ->assertCanSeeTableRecords($records->sortByDesc($column), inOrder: true);
})->with(['last_name']);

it('can search column', function (string $column) {
    $records = User::factory(5)->create();

    $value = $records->first()->{$column};

    Livewire::test(ListUsers::class)
        ->loadTable()
        ->searchTable($value)
        ->assertCanSeeTableRecords($records->where($column, $value))
        ->assertCanNotSeeTableRecords($records->where($column, '!=', $value));
})->with(['name']);

it('can update a user', function () {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->id,
    ])
        ->fillForm([
            'departments' => [DepartmentEnum::CPAS->value],
        ])
        ->call('save')
        ->assertNotified();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'departments' => json_encode([DepartmentEnum::CPAS->value]),
    ]);
});

it('validates the form data', function (array $data, array $errors) {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, [
        'record' => $user->id,
    ])
        ->fillForm([
            'departments' => [DepartmentEnum::VILLE->value],
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`departments` is required' => [['departments' => null], ['departments' => 'required']],
]);
