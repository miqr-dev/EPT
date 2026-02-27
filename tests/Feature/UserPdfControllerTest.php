<?php

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeUserForPdfTest(string $role, ?int $cityId, array $overrides = []): User
{
    return User::create(array_merge([
        'name' => 'Mustermann',
        'firstname' => 'Max',
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'role' => $role,
        'city_id' => $cityId,
        'can_login' => true,
        'contract_view_enabled' => false,
    ], $overrides));
}

test('it resolves participant pdf from normalized city subfolder with lower-case extension', function () {
    Storage::fake('pdfs');

    $city = City::create(['name' => 'Berlin TBR']);
    $teacher = makeUserForPdfTest('teacher', $city->id);
    $participant = makeUserForPdfTest('participant', $city->id, ['username' => 'alice']);

    Storage::disk('pdfs')->put('Berlin_TBR/alice.pdf', 'pdf-content');

    $this->actingAs($teacher)
        ->get(route('participants.pdf', $participant))
        ->assertOk();
});

test('it resolves participant pdf from normalized city subfolder with upper-case extension', function () {
    Storage::fake('pdfs');

    $city = City::create(['name' => 'Berlin PP']);
    $teacher = makeUserForPdfTest('teacher', $city->id);
    $participant = makeUserForPdfTest('participant', $city->id, ['username' => 'bob']);

    Storage::disk('pdfs')->put('Berlin_PP/bob.PDF', 'pdf-content');

    $this->actingAs($teacher)
        ->get(route('participants.pdf', $participant))
        ->assertOk();
});

test('it returns 404 when city is missing for participant pdf lookup', function () {
    Storage::fake('pdfs');

    $city = City::create(['name' => 'Leipzig']);
    $teacher = makeUserForPdfTest('teacher', $city->id);
    $participant = makeUserForPdfTest('participant', null, ['username' => 'charlie']);

    $this->actingAs($teacher)
        ->get(route('participants.pdf', $participant))
        ->assertNotFound();
});
