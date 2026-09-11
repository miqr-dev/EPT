<?php

use App\Models\City;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\Test;
use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createParticipantResultsListUser(array $attributes): User
{
    return User::create([
        'name' => $attributes['name'] ?? 'Muster',
        'firstname' => $attributes['firstname'] ?? 'Tina',
        'username' => $attributes['username'] ?? fake()->unique()->userName(),
        'password' => 'password',
        'role' => $attributes['role'] ?? 'participant',
        'city_id' => $attributes['city_id'] ?? null,
    ]);
}

test('participant results list keeps desktop rows while tablet selection starts empty', function () {
    $this->withoutVite();

    $city = City::create(['name' => 'Berlin']);
    $otherCity = City::create(['name' => 'Hamburg']);
    $teacher = createParticipantResultsListUser([
        'name' => 'Teacher',
        'firstname' => 'Berlin',
        'username' => 'participant.results.teacher',
        'role' => 'teacher',
        'city_id' => $city->id,
    ]);

    foreach (range(1, 6) as $index) {
        createParticipantResultsListUser([
            'name' => "Marter {$index}",
            'firstname' => 'Ada',
            'username' => "marter.{$index}",
            'city_id' => $city->id,
        ]);
    }

    createParticipantResultsListUser([
        'name' => 'Marter Other',
        'firstname' => 'Ada',
        'username' => 'marter.other',
        'city_id' => $otherCity->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('participants.list'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Participants/List')
            ->has('participants.data', 6)
            ->has('selectedParticipants', 0)
            ->has('suggestions', 0)
        );

    $this->actingAs($teacher)
        ->get(route('participants.list', ['search' => 'Ma']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Participants/List')
            ->has('participants.data', 6)
            ->has('selectedParticipants', 0)
            ->has('suggestions', 0)
        );

    $this->actingAs($teacher)
        ->get(route('participants.list', ['search' => 'Mar']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Participants/List')
            ->has('participants.data', 6)
            ->has('selectedParticipants', 0)
            ->has('suggestions', 5)
        );
});

test('participant results list shows only the selected participant and their exam results', function () {
    $this->withoutVite();

    $city = City::create(['name' => 'Berlin']);
    $teacher = createParticipantResultsListUser([
        'name' => 'Teacher',
        'firstname' => 'Berlin',
        'username' => 'participant.results.selected.teacher',
        'role' => 'teacher',
        'city_id' => $city->id,
    ]);
    $participant = createParticipantResultsListUser([
        'name' => 'Marter Selected',
        'firstname' => 'Ada',
        'username' => 'marter.selected',
        'city_id' => $city->id,
    ]);
    $otherParticipant = createParticipantResultsListUser([
        'name' => 'Marter Other',
        'firstname' => 'Ben',
        'username' => 'marter.not-selected',
        'city_id' => $city->id,
    ]);

    $shownTest = Test::create([
        'code' => 'BRT-A',
        'name' => 'BRT-A',
        'description' => 'Test',
        'duration' => 30,
    ]);
    $hiddenTest = Test::create([
        'code' => 'AVEM',
        'name' => 'AVEM',
        'description' => 'Test',
        'duration' => 30,
    ]);

    $exam = Exam::create([
        'name' => 'Selected exam',
        'city_id' => $city->id,
        'teacher_id' => $teacher->id,
        'status' => 'not_started',
    ]);
    ExamParticipant::create(['exam_id' => $exam->id, 'participant_id' => $participant->id]);
    ExamStep::create([
        'exam_id' => $exam->id,
        'test_id' => $shownTest->id,
        'step_order' => 1,
        'duration' => 30,
    ]);

    $shownAssignment = TestAssignment::create([
        'participant_id' => $participant->id,
        'test_id' => $shownTest->id,
        'status' => 'completed',
        'completed_at' => now(),
    ]);
    TestResult::create([
        'assignment_id' => $shownAssignment->id,
        'result_json' => ['score' => 42],
    ]);

    $hiddenAssignment = TestAssignment::create([
        'participant_id' => $participant->id,
        'test_id' => $hiddenTest->id,
        'status' => 'completed',
        'completed_at' => now(),
    ]);
    TestResult::create([
        'assignment_id' => $hiddenAssignment->id,
        'result_json' => ['score' => 12],
    ]);

    TestAssignment::create([
        'participant_id' => $otherParticipant->id,
        'test_id' => $shownTest->id,
        'status' => 'completed',
        'completed_at' => now(),
    ]);

    $this->actingAs($teacher)
        ->get(route('participants.list', [
            'search' => 'Marter Selected',
            'participant_id' => $participant->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Participants/List')
            ->where('filters.participant_id', $participant->id)
            ->has('suggestions', 0)
            ->has('participants.data', 1)
            ->where('participants.data.0.id', $participant->id)
            ->where('participants.data.0.name', 'Marter Selected')
            ->has('selectedParticipants', 1)
            ->where('selectedParticipants.0.id', $participant->id)
            ->where('selectedParticipants.0.name', 'Marter Selected')
            ->has('selectedParticipants.0.test_assignments', 1)
            ->where('selectedParticipants.0.test_assignments.0.test.name', 'BRT-A')
            ->has('selectedParticipants.0.test_assignments.0.results', 1)
        );
});
