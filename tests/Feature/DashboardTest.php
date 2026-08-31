<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function dashboardUser(string $username): User
{
    return User::create([
        'name' => 'Muster',
        'firstname' => 'Tina',
        'username' => $username,
        'email' => "{$username}@example.test",
        'password' => 'password',
        'role' => 'teacher',
    ]);
}

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = dashboardUser('dashboard.teacher');
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('shared props expose the active brand profile', function () {
    config(['branding.active' => 'gbbr']);

    $user = dashboardUser('dashboard.brand');
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('brand.key', 'gbbr')
            ->where('brand.entranceAnalysis.logoSrc', '/images/gbbr-logo.svg')
        );
});
