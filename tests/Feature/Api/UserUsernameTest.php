<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns 401 when unauthenticated', function () {
    $this->getJson('/api/users/usernames')
        ->assertUnauthorized();
});

it('returns a list of usernames', function () {
    Sanctum::actingAs(User::factory()->create());

    $users = User::factory(3)->create();

    $this->getJson('/api/users/usernames')
        ->assertOk()
        ->assertJsonStructure(['data'])
        ->assertJsonCount(4, 'data'); // 3 created + 1 acting as
});
