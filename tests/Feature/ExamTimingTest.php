<?php

use App\Models\City;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\ExamStepStatus;
use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('active exam timing uses the configured exam step duration for BT', function () {
    $city = City::create(['name' => 'Berlin']);
    $teacher = User::create([
        'name' => 'Teacher',
        'firstname' => 'Tina',
        'username' => 'teacher.timing',
        'email' => 'teacher.timing@example.test',
        'password' => 'password',
        'role' => 'teacher',
        'city_id' => $city->id,
    ]);
    $participant = User::create([
        'name' => 'Participant',
        'firstname' => 'Pat',
        'username' => 'participant.timing',
        'email' => 'participant.timing@example.test',
        'password' => 'password',
        'role' => 'participant',
        'city_id' => $city->id,
    ]);

    $bt = Test::create([
        'code' => 'BT',
        'name' => 'BT',
        'description' => 'Buerotest',
        'duration' => 60,
    ]);

    $exam = Exam::create([
        'name' => 'BT timing',
        'city_id' => $city->id,
        'teacher_id' => $teacher->id,
        'status' => 'in_progress',
    ]);

    $step = ExamStep::create([
        'exam_id' => $exam->id,
        'test_id' => $bt->id,
        'step_order' => 1,
        'duration' => 25,
    ]);

    $exam->update(['current_exam_step_id' => $step->id]);
    ExamParticipant::create(['exam_id' => $exam->id, 'participant_id' => $participant->id]);
    ExamStepStatus::create([
        'exam_id' => $exam->id,
        'exam_step_id' => $step->id,
        'participant_id' => $participant->id,
        'status' => 'not_started',
    ]);

    $response = $this->actingAs($teacher)->getJson(route('api.active-exams'));

    $response->assertOk();
    $payload = $response->json();
    $status = collect($payload[0]['participants'][0]['step_statuses'])
        ->firstWhere('exam_step_id', $step->id);

    expect($payload[0]['current_step']['duration'])->toBe(25);
    expect($payload[0]['current_step']['test']['duration'])->toBe(60);
    expect($status['time_remaining'])->toBe(25 * 60);
});
