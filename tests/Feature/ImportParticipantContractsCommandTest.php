<?php

use App\Models\City;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function participantForContract(City $city, array $overrides = []): User
{
    return User::create(array_merge([
        'name' => '',
        'firstname' => '',
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'role' => 'participant',
        'city_id' => $city->id,
        'can_login' => true,
    ], $overrides));
}

function contractPdfText(string $fullName = 'Simone Muster'): string
{
    return <<<PDF
stream
BT
(und Frau/Herrn {$fullName} als Teilnehmer) Tj
(Lehrgang OSI - Orientierung, Schulung und Integration) Tj
(Ort Erfurt) Tj
(Dauer Beginn: 02.03.2026 Ende: 01.03.2027) Tj
(Maßnahmezeit 40 Unterrichtseinheiten pro Woche) Tj
(Geburtsdatum: 14.03.1970) Tj
(Straße, Hausnummer: Musterweg 10) Tj
(PLZ, Ort: 99084 Erfurt) Tj
(Tel.: 01234 56789) Tj
(Leistungsträger Jobcenter Erfurt) Tj
ET
endstream
PDF;
}

test('it imports contract values and only fills missing profile data', function () {
    Storage::fake('pdfs');
    Storage::fake('local');

    $city = City::create(['name' => 'Erfurt']);
    $participant = participantForContract($city, ['username' => 'simone']);

    ParticipantProfile::create([
        'user_id' => $participant->id,
        'birthday' => '1970-03-14',
        'age' => 54,
        'sex' => 'w',
        'street' => 'Bestehende Straße 1',
    ]);

    Storage::disk('pdfs')->put('Erfurt/simone.pdf', contractPdfText());

    $this->artisan('participants:import-contracts', ['--report' => 'reports/contract-test.json'])
        ->assertSuccessful();

    $participant->refresh();
    $profile = $participant->participantProfile()->firstOrFail();

    expect($participant->firstname)->toBe('Simone')
        ->and($participant->name)->toBe('Muster')
        ->and($profile->street)->toBe('Bestehende Straße 1')
        ->and($profile->zip)->toBe('99084')
        ->and($profile->cost_bearer)->toBe('Jobcenter Erfurt')
        ->and($profile->contract_imported_at)->not->toBeNull();

    Storage::disk('local')->assertExists('reports/contract-test.json');
});

test('it ignores already imported contracts', function () {
    Storage::fake('pdfs');

    $city = City::create(['name' => 'Erfurt']);
    $participant = participantForContract($city, ['username' => 'already']);

    ParticipantProfile::create([
        'user_id' => $participant->id,
        'birthday' => '1970-03-14',
        'age' => 54,
        'sex' => 'w',
        'contract_imported_at' => now()->subDay(),
    ]);

    Storage::disk('pdfs')->put('Erfurt/already.pdf', contractPdfText('Anna Alt'));

    $this->artisan('participants:import-contracts')->assertSuccessful();

    $participant->refresh();
    expect($participant->firstname)->toBe('');
});


test('it supports dry run without persisting imported values', function () {
    Storage::fake('pdfs');

    $city = City::create(['name' => 'Erfurt']);
    $participant = participantForContract($city, ['username' => 'dryrun']);

    ParticipantProfile::create([
        'user_id' => $participant->id,
        'birthday' => '1970-03-14',
        'age' => 54,
        'sex' => 'w',
    ]);

    Storage::disk('pdfs')->put('Erfurt/dryrun.pdf', contractPdfText());

    $this->artisan('participants:import-contracts', ['--dry-run' => true])->assertSuccessful();

    $participant->refresh();
    $profile = $participant->participantProfile()->firstOrFail();

    expect($participant->firstname)->toBe('')
        ->and($profile->cost_bearer)->toBeNull()
        ->and($profile->contract_imported_at)->toBeNull();
});
