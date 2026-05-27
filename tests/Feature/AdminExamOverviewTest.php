<?php

use App\Models\City;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\Test as ExamTest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('admins can see ongoing and planned exams grouped by standort', function () {
    $berlin = City::create(['name' => 'Berlin']);
    $hamburg = City::create(['name' => 'Hamburg']);

    $createUser = fn (array $attributes) => User::create([
        'password' => 'password',
        ...$attributes,
    ]);

    $admin = $createUser([
        'name' => 'Admin',
        'firstname' => 'One',
        'username' => 'admin.one',
        'role' => 'admin',
        'city_id' => $berlin->id,
    ]);

    $berlinTeacher = $createUser([
        'name' => 'Teacher',
        'firstname' => 'Berlin',
        'username' => 'teacher.berlin',
        'role' => 'teacher',
        'city_id' => $berlin->id,
    ]);

    $hamburgTeacher = $createUser([
        'name' => 'Teacher',
        'firstname' => 'Hamburg',
        'username' => 'teacher.hamburg',
        'role' => 'teacher',
        'city_id' => $hamburg->id,
    ]);

    $participant = $createUser([
        'name' => 'Participant',
        'firstname' => 'One',
        'username' => 'participant.one',
        'role' => 'participant',
        'city_id' => $berlin->id,
    ]);

    $test = ExamTest::create([
        'code' => 'BRT-A',
        'name' => 'BRT-A',
        'description' => 'Test',
        'duration' => 30,
    ]);

    $ongoingExam = Exam::create([
        'name' => 'Berlin laufend',
        'city_id' => $berlin->id,
        'teacher_id' => $berlinTeacher->id,
        'status' => 'in_progress',
        'started_at' => now()->subMinutes(10),
    ]);

    $ongoingStep = ExamStep::create([
        'exam_id' => $ongoingExam->id,
        'test_id' => $test->id,
        'step_order' => 1,
        'duration' => 30,
    ]);

    $ongoingExam->update(['current_exam_step_id' => $ongoingStep->id]);
    ExamParticipant::create(['exam_id' => $ongoingExam->id, 'participant_id' => $participant->id]);

    $plannedBerlinExam = Exam::create([
        'name' => 'Berlin geplant',
        'city_id' => $berlin->id,
        'teacher_id' => $berlinTeacher->id,
        'status' => 'not_started',
    ]);

    ExamStep::create([
        'exam_id' => $plannedBerlinExam->id,
        'test_id' => $test->id,
        'step_order' => 1,
        'duration' => 30,
    ]);

    Exam::create([
        'name' => 'Berlin abgeschlossen',
        'city_id' => $berlin->id,
        'teacher_id' => $berlinTeacher->id,
        'status' => 'completed ',
    ]);

    $plannedHamburgExam = Exam::create([
        'name' => 'Hamburg geplant',
        'city_id' => $hamburg->id,
        'teacher_id' => $hamburgTeacher->id,
        'status' => 'not_started',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.exam-overview'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ExamOverview')
            ->where('summary.locationCount', 2)
            ->where('summary.ongoingCount', 1)
            ->where('summary.plannedCount', 2)
            ->where('locations.0.name', 'Berlin')
            ->has('locations.0.ongoingExams', 1)
            ->where('locations.0.ongoingExams.0.id', $ongoingExam->id)
            ->has('locations.0.plannedExams', 1)
            ->where('locations.0.plannedExams.0.id', $plannedBerlinExam->id)
            ->where('locations.1.name', 'Hamburg')
            ->has('locations.1.ongoingExams', 0)
            ->has('locations.1.plannedExams', 1)
            ->where('locations.1.plannedExams.0.id', $plannedHamburgExam->id)
        );
});

test('teachers cannot access the admin exam overview', function () {
    $city = City::create(['name' => 'Berlin']);

    $teacher = User::create([
        'name' => 'Teacher',
        'firstname' => 'One',
        'username' => 'teacher.one',
        'password' => 'password',
        'role' => 'teacher',
        'city_id' => $city->id,
    ]);

    $this->actingAs($teacher)
        ->get(route('admin.exam-overview'))
        ->assertForbidden();
});
