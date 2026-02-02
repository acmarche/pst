<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('an unauthenticated user can access the login page', function () {
    $this->get(Filament::getLoginUrl())
        ->assertOk();
});

test('an unauthenticated user can not access the admin panel', function () {
    $this->get('admin')
        ->assertRedirect(Filament::getLoginUrl());
});

test('an unauthenticated user can login', function () {
    Filament::setCurrentPanel(
        Filament::getPanel('admin'),
    );

    Livewire::test(Login::class)
        ->fillForm([
            'email' => $this->user->username,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors();
});

test('an authenticated user can access the admin panel', function () {
    $this->actingAs($this->user)
        ->get('admin')
        ->assertOk();
});

test('an authenticated user can logout', function () {
    $this->actingAs($this->user);

    $this->assertAuthenticated();

    $this->post(Filament::getLogoutUrl())
        ->assertRedirect(Filament::getLoginUrl());
});
