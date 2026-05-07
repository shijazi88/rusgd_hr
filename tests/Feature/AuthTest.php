<?php

use App\Models\Employee;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Authentication', function () {

    it('allows login with valid credentials', function () {
        $emp = Employee::factory()->create();
        User::factory()->create([
            'employee_id' => $emp->id,
            'email'       => 'auth@rushdai.com',
            'password'    => bcrypt('password'),
        ]);

        postJson('/api/v1/auth/login', [
            'email'    => 'auth@rushdai.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure(['success', 'data' => ['token', 'user']])
            ->assertJsonPath('success', true);
    });

    it('rejects login with wrong password', function () {
        $emp = Employee::factory()->create();
        User::factory()->create(['employee_id' => $emp->id, 'email' => 'x@r.com']);

        postJson('/api/v1/auth/login', ['email' => 'x@r.com', 'password' => 'wrongpassword'])
            ->assertUnauthorized();
    });

    it('rejects login with unknown email', function () {
        postJson('/api/v1/auth/login', ['email' => 'nobody@r.com', 'password' => 'password'])
            ->assertUnauthorized();
    });

    it('validates required fields on login and returns success:false', function () {
        postJson('/api/v1/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    });

    it('returns current user on /me', function () {
        $user = userWith(['view_employees']);
        Sanctum::actingAs($user);

        getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', $user->email);
    });

    it('rejects /me when unauthenticated', function () {
        getJson('/api/v1/auth/me')->assertUnauthorized();
    });

    it('allows logout', function () {
        $user = userWith();
        Sanctum::actingAs($user);

        postJson('/api/v1/auth/logout')->assertOk()->assertJsonPath('success', true);
    });

    it('rejects logout when unauthenticated', function () {
        postJson('/api/v1/auth/logout')->assertUnauthorized();
    });
});
