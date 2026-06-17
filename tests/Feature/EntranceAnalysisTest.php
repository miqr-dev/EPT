<?php

use App\Models\City;
use App\Models\EntranceAnalysis;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\ParticipantProfile;
use App\Models\Test;
use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function entranceAnalysisUser(City $city, string $role, string $username): User
{
    return User::create([
        'name' => $role === 'participant' ? 'Mustermann' : 'Muster',
        'firstname' => $role === 'participant' ? 'Max' : 'Tina',
        'username' => $username,
        'password' => 'password',
        'role' => $role,
        'city_id' => $city->id,
    ]);
}

test('teacher can save participant entrance analysis observations', function () {
    $city = City::create(['name' => 'Berlin']);
    $teacher = entranceAnalysisUser($city, 'teacher', 'teacher.entrance');
    $participant = entranceAnalysisUser($city, 'participant', 'participant.entrance');

    $this->actingAs($teacher)
        ->put(route('participants.entrance-analysis.update', $participant), [
            'instruction_understanding' => 'Versteht mehrteilige Aufgaben.',
            'work_method' => 'Arbeitet strukturiert.',
            'work_speed' => 'Angemessen.',
            'group_behavior' => 'Kooperativ.',
            'remarks' => 'Benötigt bei neuen Aufgaben kurze Rückfragen.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('entrance_analyses', [
        'participant_id' => $participant->id,
        'teacher_id' => $teacher->id,
        'work_method' => 'Arbeitet strukturiert.',
    ]);
});

test('teacher cannot edit an entrance analysis from another city', function () {
    $berlin = City::create(['name' => 'Berlin']);
    $hamburg = City::create(['name' => 'Hamburg']);
    $teacher = entranceAnalysisUser($berlin, 'teacher', 'teacher.berlin');
    $participant = entranceAnalysisUser($hamburg, 'participant', 'participant.hamburg');

    $this->actingAs($teacher)
        ->put(route('participants.entrance-analysis.update', $participant), [
            'remarks' => 'Nicht erlaubt',
        ])
        ->assertForbidden();
});

test('entrance analysis print page contains saved observations and latest test data', function () {
    $city = City::create(['name' => 'Berlin']);
    $teacher = entranceAnalysisUser($city, 'teacher', 'teacher.print');
    $participant = entranceAnalysisUser($city, 'participant', 'participant.print');

    ParticipantProfile::create([
        'user_id' => $participant->id,
        'birthday' => '1990-05-12',
        'age' => 36,
        'sex' => 'm',
    ]);

    $test = Test::create([
        'code' => 'BRT-A',
        'name' => 'BRT-A',
        'duration' => 6,
    ]);
    $exam = Exam::create([
        'name' => 'Eingangstest',
        'city_id' => $city->id,
        'teacher_id' => $teacher->id,
    ]);
    $exam->forceFill(['created_at' => '2026-06-01 09:00:00'])->save();

    ExamParticipant::create([
        'exam_id' => $exam->id,
        'participant_id' => $participant->id,
    ]);
    ExamStep::create([
        'exam_id' => $exam->id,
        'test_id' => $test->id,
        'step_order' => 1,
        'duration' => 6,
    ]);
    $assignment = TestAssignment::create([
        'participant_id' => $participant->id,
        'test_id' => $test->id,
        'status' => 'completed',
    ]);
    TestResult::create([
        'assignment_id' => $assignment->id,
        'teacher_id' => $teacher->id,
        'result_json' => [
            'rohwert' => 12,
            'prozentrang' => 70,
            'twert' => 56,
            'answers' => [],
        ],
    ]);
    EntranceAnalysis::create([
        'participant_id' => $participant->id,
        'teacher_id' => $teacher->id,
        'instruction_understanding' => 'Sicher',
        'remarks' => 'Beobachtung',
    ]);

    $this->actingAs($teacher)
        ->get(route('participants.entrance-analysis.print', $participant))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Print/EntranceAnalysis')
            ->where('filename', 'Mustermann_Eingangsanalyse.pdf')
            ->where('teacherName', 'Tina Muster')
            ->where('conductedAt', '2026-06-01')
            ->where('analysis.instruction_understanding', 'Sicher')
            ->where('assignments.0.test.name', 'BRT-A')
            ->where('assignments.0.results.0.result_json.twert', 56)
        );
});
