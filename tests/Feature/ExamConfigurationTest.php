<?php

use App\Models\City;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\ExamStepStatus;
use App\Models\Test;
use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function makeUser(string $role, ?int $cityId = null): User
{
    return User::factory()->create([
        'firstname' => fake()->firstName(),
        'username' => fake()->unique()->userName(),
        'role' => $role,
        'city_id' => $cityId,
    ]);
}

test('it updates participants during an ongoing exam and syncs step statuses for added and removed participants', function () {
    $city = City::create(['name' => 'Zürich']);
    $teacher = makeUser('teacher', $city->id);

    $test = Test::create([
        'code' => 'MRT-A',
        'name' => 'MRT-A',
        'description' => 'Test',
        'duration' => 30,
    ]);

    $existingParticipant = makeUser('participant', $city->id);
    $removedParticipant = makeUser('participant', $city->id);
    $addedParticipantWithHistory = makeUser('participant', $city->id);

    $exam = Exam::create([
        'name' => 'Ongoing exam',
        'city_id' => $city->id,
        'teacher_id' => $teacher->id,
        'status' => 'in_progress',
    ]);

    $step = ExamStep::create([
        'exam_id' => $exam->id,
        'test_id' => $test->id,
        'step_order' => 1,
        'duration' => 30,
    ]);

    $exam->update(['current_exam_step_id' => $step->id]);

    ExamParticipant::create(['exam_id' => $exam->id, 'participant_id' => $existingParticipant->id]);
    ExamParticipant::create(['exam_id' => $exam->id, 'participant_id' => $removedParticipant->id]);

    ExamStepStatus::create([
        'exam_id' => $exam->id,
        'exam_step_id' => $step->id,
        'participant_id' => $existingParticipant->id,
        'status' => 'not_started',
    ]);

    ExamStepStatus::create([
        'exam_id' => $exam->id,
        'exam_step_id' => $step->id,
        'participant_id' => $removedParticipant->id,
        'status' => 'in_progress',
    ]);

    $assignment = TestAssignment::create([
        'participant_id' => $addedParticipantWithHistory->id,
        'test_id' => $test->id,
        'status' => 'completed',
        'completed_at' => now()->subMinute(),
    ]);
    TestResult::create([
        'assignment_id' => $assignment->id,
        'result_json' => ['ok' => true],
    ]);

    $this->actingAs($teacher)
        ->put(route('exams.updateConfiguration', $exam), [
            'participant_ids' => [$existingParticipant->id, $addedParticipantWithHistory->id],
        ])
        ->assertStatus(303)
        ->assertSessionHas('success');

    expect(ExamParticipant::where('exam_id', $exam->id)->pluck('participant_id')->all())
        ->toEqualCanonicalizing([$existingParticipant->id, $addedParticipantWithHistory->id]);

    expect(ExamStepStatus::where('exam_id', $exam->id)
        ->where('participant_id', $removedParticipant->id)
        ->exists())->toBeFalse();

    $addedStatus = ExamStepStatus::where('exam_id', $exam->id)
        ->where('participant_id', $addedParticipantWithHistory->id)
        ->where('exam_step_id', $step->id)
        ->first();

    expect($addedStatus)->not->toBeNull();
    expect($addedStatus->status)->toBe('completed');
    expect($addedStatus->completed_at)->not->toBeNull();
});

test('it rejects participant changes for completed exams', function () {
    $city = City::create(['name' => 'Bern']);
    $teacher = makeUser('teacher', $city->id);
    $participant = makeUser('participant', $city->id);

    $exam = Exam::create([
        'name' => 'Completed exam',
        'city_id' => $city->id,
        'teacher_id' => $teacher->id,
        'status' => 'completed',
    ]);

    $this->actingAs($teacher)
        ->put(route('exams.updateConfiguration', $exam), [
            'participant_ids' => [$participant->id],
        ])
        ->assertStatus(303)
        ->assertSessionHas('error');
});
