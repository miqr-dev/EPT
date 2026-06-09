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

test('participant result print can interleave answer sheets', function () {
    $city = City::create(['name' => 'Berlin']);
    $teacher = User::create([
        'name' => 'Teacher',
        'firstname' => 'Tina',
        'username' => 'teacher.results',
        'password' => 'password',
        'role' => 'teacher',
        'city_id' => $city->id,
    ]);
    $participant = User::create([
        'name' => 'Max Mustermann',
        'firstname' => 'Max',
        'username' => 'participant.results',
        'password' => 'password',
        'role' => 'participant',
        'city_id' => $city->id,
    ]);
    $test = Test::create([
        'code' => 'BRT-A',
        'name' => 'BRT-A',
        'duration' => 6,
    ]);
    $exam = Exam::create([
        'name' => 'PDF Test',
        'city_id' => $city->id,
        'teacher_id' => $teacher->id,
    ]);

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
    $testResult = TestResult::create([
        'assignment_id' => $assignment->id,
        'result_json' => [
            'rohwert' => 1,
            'answers' => [
                [
                    'question' => 'A + B',
                    'user_answer' => 'C',
                    'correct_answers' => ['C'],
                    'is_correct' => true,
                ],
            ],
        ],
    ]);

    $this->actingAs($teacher)
        ->get(route('test-results.print', $testResult))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Print/TestResults')
            ->where('includeAnswers', false)
            ->where('filename', 'Max_Mustermann_BRT-A_Ergebnis.pdf')
            ->where('pdfUrl', fn (string $url) => !str_contains($url, 'include_answers'))
            ->has('assignments', 1)
            ->where('assignments.0.results.0.id', $testResult->id)
        );

    $this->actingAs($teacher)
        ->get(route('test-results.print', [
            'testResult' => $testResult,
            'include_answers' => 1,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Print/TestResults')
            ->where('includeAnswers', true)
            ->where('filename', 'Max_Mustermann_BRT-A_Ergebnis_und_Antworten.pdf')
            ->where('pdfUrl', fn (string $url) => str_contains($url, 'include_answers=1'))
            ->has('assignments', 1)
            ->where('assignments.0.results.0.id', $testResult->id)
            ->has('assignments.0.results.0.result_json.answers', 1)
        );

    $this->actingAs($teacher)
        ->get(route('participants.results.print', $participant))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Print/TestResults')
            ->where('includeAnswers', false)
            ->where('filename', 'Max_Mustermann_Alle_Tests.pdf')
        );

    $this->actingAs($teacher)
        ->get(route('participants.results.print', [
            'participant' => $participant,
            'include_answers' => 1,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Print/TestResults')
            ->where('includeAnswers', true)
            ->where('filename', 'Max_Mustermann_Alle_Tests_und_Antworten.pdf')
            ->where('pdfUrl', fn (string $url) => str_contains($url, 'include_answers=1'))
            ->has('assignments', 1)
            ->where('assignments.0.test.name', 'BRT-A')
            ->has('assignments.0.results.0.result_json.answers', 1)
        );
});
