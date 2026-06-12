<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createInstructionsTeacher(): User
{
    return User::create([
        'name' => 'Teacher',
        'firstname' => 'Test',
        'username' => 'teacher.instructions',
        'password' => 'password',
        'role' => 'teacher',
        'can_login' => true,
    ]);
}

test('guests are redirected from test instructions to login', function () {
    $this->get('/anleitungen/brt')->assertRedirect('/login');
});

test('teachers can view each test instruction page', function (string $test) {
    $teacher = createInstructionsTeacher();

    $this->actingAs($teacher)
        ->get("/anleitungen/{$test}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Instructions/Show')
            ->where('test', $test));
})->with([
    'allgemein',
    'brt',
    'mrt',
    'fpi-r',
    'lmt',
    'lps',
    'bit-2',
    'bt',
    'avem',
    '628',
]);

test('instructions index redirects to general information', function () {
    $teacher = createInstructionsTeacher();

    $this->actingAs($teacher)
        ->get('/anleitungen')
        ->assertRedirect('/anleitungen/allgemein');
});

test('unknown test instructions return not found', function () {
    $teacher = createInstructionsTeacher();

    $this->actingAs($teacher)
        ->get('/anleitungen/unbekannt')
        ->assertNotFound();
});
