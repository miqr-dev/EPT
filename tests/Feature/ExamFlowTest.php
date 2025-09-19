<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamStep;
use App\Models\ExamStepStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_pause_and_resume_a_test()
    {
        // 1. Create a teacher, a participant, an exam with one step.
        $teacher = User::factory()->create(['role' => 'teacher']);
        $participant = User::factory()->create(['role' => 'participant']);
        $exam = Exam::factory()->create(['teacher_id' => $teacher->id]);
        $exam->participants()->attach($participant->id);
        $step = ExamStep::factory()->create(['exam_id' => $exam->id]);

        // 2. Start the exam.
        $this->actingAs($teacher);
        $this->post(route('exams.start', $exam));

        // 3. As the teacher, enable the pause functionality for the step.
        $this->post(route('exams.toggle-pause', $exam), [
            'step_id' => $step->id,
            'pause_enabled' => true,
        ]);

        // 4. As the participant, start the test.
        $this->actingAs($participant);
        $this->post(route('my-exam.start-step'), ['exam_step_id' => $step->id]);

        // 5. As the participant, pause the test.
        $this->post(route('my-exam.pause-step'), ['exam_step_id' => $step->id]);

        // 6. Verify that the test is paused.
        $status = ExamStepStatus::where('exam_step_id', $step->id)
            ->where('participant_id', $participant->id)
            ->first();
        $this->assertEquals('paused', $status->status);

        // 7. As the participant, resume the test.
        $this->post(route('my-exam.start-step'), ['exam_step_id' => $step->id]);

        // 8. Verify that the test is resumed.
        $status->refresh();
        $this->assertEquals('in_progress', $status->status);

        // 9. As the participant, complete the test.
        $this->post(route('my-exam.complete-step'), [
            'exam_step_id' => $step->id,
            'results' => ['foo' => 'bar'],
        ]);

        // 10. Verify that the test is completed.
        $status->refresh();
        $this->assertEquals('completed', $status->status);
    }
}
