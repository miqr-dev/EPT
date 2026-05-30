<?php

use App\Models\City;
use App\Models\ParticipantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeContractUser(string $role, ?int $cityId, array $overrides = []): User
{
    return User::create(array_merge([
        'name' => '',
        'firstname' => '',
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'role' => $role,
        'city_id' => $cityId,
        'can_login' => true,
        'contract_view_enabled' => false,
    ], $overrides));
}

function contractPdfFixture(string $fullName = 'Simone Muster'): string
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

test('it imports participant contract when teacher enables contract visibility', function () {
    Storage::fake('pdfs');

    $city = City::create(['name' => 'Erfurt']);
    $teacher = makeContractUser('teacher', $city->id);
    $participant = makeContractUser('participant', $city->id, ['username' => 'simone']);

    ParticipantProfile::create([
        'user_id' => $participant->id,
        'birthday' => '1970-03-14',
        'age' => 54,
        'sex' => 'w',
    ]);

    Storage::disk('pdfs')->put('Erfurt/simone.pdf', contractPdfFixture());

    $this->actingAs($teacher)
        ->post(route('participants.set-contract-visibility', $participant), [
            'enabled' => true,
        ])
        ->assertRedirect();

    $participant->refresh();
    $profile = $participant->participantProfile()->firstOrFail();

    expect($participant->contract_view_enabled)->toBeTrue()
        ->and($participant->firstname)->toBe('Simone')
        ->and($participant->name)->toBe('Muster')
        ->and($profile->cost_bearer)->toBe('Jobcenter Erfurt')
        ->and($profile->contract_imported_at)->not->toBeNull();
});
