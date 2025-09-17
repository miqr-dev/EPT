<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\ExamStepStatus;
use App\Models\ExamPauseResult;
use App\Models\User;
use App\Models\Test;
use App\Models\City;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ExamController extends Controller
{
  // 1. List all exams (for teachers/admins)
  public function index()
  {
    $exams = Exam::with(['city', 'teacher', 'participants.user', 'steps.test'])->get();

    return Inertia::render('Exams/Index', [
      'exams' => $exams
    ]);
  }

  // 2. Show form for creating new exam (teacher/admin)
  public function create()
  {
    return Inertia::render('Exams/Create', [
      'cities' => City::all(),
      'teachers' => User::where('role', 'teacher')->get(),
      'tests' => Test::all()
    ]);
  }

  // 3. Store newly created exam (+ steps)
  public function store(Request $request)
  {
    $data = $request->validate([
      'name' => 'required',
      'city_id' => 'required|exists:cities,id',
      'teacher_id' => 'required|exists:users,id',
      'steps' => 'required|array|min:1',
      'steps.*.test_id' => 'required|exists:tests,id',
      'steps.*.duration' => 'required|integer|min:1',
    ]);

    $exam = Exam::create([
      'name' => $data['name'],
      'city_id' => $data['city_id'],
      'teacher_id' => $data['teacher_id'],
      'status' => 'not_started',
    ]);

    // Create steps
    foreach ($data['steps'] as $idx => $step) {
      ExamStep::create([
        'exam_id' => $exam->id,
        'test_id' => $step['test_id'],
        'step_order' => $idx + 1,
        'duration' => $step['duration'],
      ]);
    }

    return redirect()->route('exams.index')->with('success', 'Exam created!');
  }

  public function storeWithParticipants(Request $request): RedirectResponse
  {
    $data = $request->validate([
      'title' => 'required|string|max:255',
      'participant_ids' => 'required|array',
      'participant_ids.*' => 'exists:users,id',
      'steps' => 'sometimes|array',
      'steps.*' => 'exists:tests,id',
    ]);

    $teacher = Auth::user();

    // Find the city from the first participant if the teacher doesn't have one.
    $cityId = $teacher->city_id;
    if (!$cityId && count($data['participant_ids']) > 0) {
      $firstParticipant = User::find($data['participant_ids'][0]);
      $cityId = $firstParticipant->city_id;
    }

    $exam = Exam::create([
      'name' => $data['title'],
      'teacher_id' => $teacher->id,
      'city_id' => $cityId,
      'status' => 'not_started',
    ]);

    foreach ($data['participant_ids'] as $participantId) {
      ExamParticipant::create([
        'exam_id' => $exam->id,
        'participant_id' => $participantId,
      ]);
    }

    if (isset($data['steps'])) {
      $tests = Test::findMany($data['steps']);
      foreach ($tests as $index => $test) {
        ExamStep::create([
          'exam_id' => $exam->id,
          'test_id' => $test->id,
          'step_order' => $index + 1,
          'duration' => $test->duration ?? 60, // Default to 60 mins if not set
        ]);
      }
    }

    return redirect()->route('dashboard')->with('success', 'Exam created successfully!');
  }


  // 4. Show exam details (with step/participant management)
  public function show(Exam $exam)
  {
    $exam->load(['city', 'teacher', 'participants.user', 'steps.test', 'currentStep']);

    if ($exam->currentStep) {
      $exam->load(['participants.stepStatuses' => function ($query) use ($exam) {
        $query->where('exam_step_id', $exam->current_exam_step_id);
      }]);
    }

    $existingParticipantIds = $exam->participants->pluck('participant_id');
    $availableParticipants = User::where('role', 'participant')
      ->where('city_id', $exam->city_id)
      ->whereNotIn('id', $existingParticipantIds)
      ->get();

    return Inertia::render('Exams/Show', [
      'exam' => $exam,
      'availableParticipants' => $availableParticipants
    ]);
  }

  // 5. Add/remove participants
  public function addParticipants(Request $request, Exam $exam)
  {
    $data = $request->validate([
      'participant_ids' => 'required|array|min:1',
      'participant_ids.*' => 'exists:users,id'
    ]);
    foreach ($data['participant_ids'] as $pid) {
      ExamParticipant::firstOrCreate([
        'exam_id' => $exam->id,
        'participant_id' => $pid,
      ]);
    }
    // Optionally: create ExamStepStatus rows for each
    return back(303)->with('success', 'Teilnehmer hinzugefügt!');
  }

  // 6. Flow controls (start, pause, resume, next step, extra time)
  public function start(Exam $exam)
  {
    if ($exam->status !== 'not_started') {
      return back(303)->with('error', 'Exam already started.');
    }

    if (!$exam->steps()->exists()) {
      return back(303)->with('error', 'Exam has no steps.');
    }

    $firstStep = $exam->steps()->orderBy('step_order')->first();

    $exam->update([
      'status' => 'in_progress',
      'current_exam_step_id' => $firstStep->id,
      'started_at' => now(),
    ]);

    // Create status records for all participants for the first step
    $exam->load('participants');
    foreach ($exam->participants as $participant) {
      ExamStepStatus::create([
        'exam_id' => $exam->id,
        'exam_step_id' => $firstStep->id,
        'participant_id' => $participant->participant_id,
        'status' => 'not_started',
      ]);
    }
    return back(303)->with('success', 'Exam started!');
  }

  public function nextStep(Exam $exam)
  {
    $currentStepOrder = $exam->currentStep->step_order;
    $nextStep = $exam->steps()->where('step_order', '>', $currentStepOrder)->orderBy('step_order')->first();

    if ($nextStep) {
      $exam->update(['current_exam_step_id' => $nextStep->id]);

      // Create status records for all participants for the new step
      $exam->load('participants');
      foreach ($exam->participants as $participant) {
        ExamStepStatus::create([
          'exam_id' => $exam->id,
          'exam_step_id' => $nextStep->id,
          'participant_id' => $participant->participant_id,
          'status' => 'not_started',
        ]);
      }
      return back(303)->with('success', 'Moved to next step.');
    } else {
      $exam->update(['status' => 'completed', 'completed_at' => now()]);
      return back(303)->with('success', 'Exam completed!');
    }
  }

  public function setStatus(Request $request, Exam $exam)
  {
    $data = $request->validate([
      'status' => 'required|in:not_started,in_progress,paused,completed',
    ]);

    $exam->update(['status' => $data['status']]);

    return back(303)->with('success', 'Exam status updated.');
  }

  public function setStep(Request $request, Exam $exam)
  {
    $data = $request->validate([
      'step_id' => 'required|exists:exam_steps,id',
    ]);

    $exam->update(['current_exam_step_id' => $data['step_id']]);

    // Create status records for all participants for the new step
    $exam->load('participants');
    foreach ($exam->participants as $participant) {
      ExamStepStatus::firstOrCreate([
        'exam_id' => $exam->id,
        'exam_step_id' => $data['step_id'],
        'participant_id' => $participant->participant_id,
      ], [
        'status' => 'not_started',
      ]);
    }

    return back(303)->with('success', 'Moved to selected step.');
  }

  public function getActiveExams()
  {
    $activeExams = Exam::with(['steps.test', 'currentStep.test'])
      ->whereIn('status', ['in_progress', 'paused'])
      ->get();

    if ($activeExams->isEmpty()) {
      return response()->json([]);
    }

    foreach ($activeExams as $activeExam) {
      $activeExam->load(['participants.user', 'participants.stepStatuses' => function ($query) use ($activeExam) {
        $query->where('exam_id', $activeExam->id);
      }]);

      if ($activeExam->currentStep) {
        $duration = $activeExam->currentStep->duration; // duration in minutes
        foreach ($activeExam->participants as $participant) {
          $status = $participant->stepStatuses->where('exam_step_id', $activeExam->current_exam_step_id)->first();

          if (!$status) {
            $status = ExamStepStatus::create([
              'exam_id' => $activeExam->id,
              'exam_step_id' => $activeExam->current_exam_step_id,
              'participant_id' => $participant->participant_id,
              'status' => 'not_started',
            ]);
            // Manually add to collection to avoid re-query
            $participant->stepStatuses->push($status);
          }

          $totalDurationSeconds = max(0, ($duration ?? 0) * 60
            + (int) $status->extra_time * 60
            + (int) $status->grace_period_seconds);

          if ($status->status === 'paused') {
            $status->time_remaining = $status->time_remaining_seconds ?? $totalDurationSeconds;
          } elseif ($status->status === 'not_started') {
            $status->time_remaining = $totalDurationSeconds;
          } elseif ($status->started_at) {
            $startTime = Carbon::parse($status->started_at);
            $endTime = $startTime->copy()->addSeconds($totalDurationSeconds);
            $status->time_remaining = now()->diffInSeconds($endTime, false);
          }

          if (isset($status->time_remaining)) {
            $status->time_remaining = max(0, (int) $status->time_remaining);
          }
        }
      }
    }

    return response()->json($activeExams);
  }
  public function setParticipantStepStatus(Request $request, Exam $exam, User $participant)
  {
    $data = $request->validate([
      'action' => 'required|in:pause,resume',
    ]);

    if (!$exam->current_exam_step_id) {
      return back(303)->with('error', 'Prüfung hat keinen aktiven Test.');
    }

    $status = ExamStepStatus::firstOrCreate([
      'exam_id' => $exam->id,
      'participant_id' => $participant->id,
      'exam_step_id' => $exam->current_exam_step_id,
    ], [
      'status' => 'not_started',
    ]);

    $status->loadMissing('step');
    $stepDurationMinutes = $status->step->duration ?? $exam->currentStep?->duration ?? 0;
    $totalDurationSeconds = max(0, (int) $stepDurationMinutes * 60
      + (int) $status->extra_time * 60
      + (int) $status->grace_period_seconds);

    if ($data['action'] === 'pause') {
      if (in_array($status->status, ['completed', 'broken'], true)) {
        return back(303)->with('error', 'Dieser Test kann nicht pausiert werden.');
      }

      if ($status->status === 'paused') {
        return back(303);
      }

      $timeRemaining = $totalDurationSeconds;

      if ($status->status === 'in_progress' && $status->started_at) {
        $startTime = Carbon::parse($status->started_at);
        $endTime = $startTime->copy()->addSeconds($totalDurationSeconds);
        $timeRemaining = now()->diffInSeconds($endTime, false);
      }

      $timeRemaining = max(0, (int) $timeRemaining);

      $previousPauseResult = ExamPauseResult::where('exam_id', $exam->id)
        ->where('exam_step_id', $status->exam_step_id)
        ->where('participant_id', $participant->id)
        ->first();
      $previousUpdatedAt = $previousPauseResult?->updated_at;

      $status->update([
        'status' => 'paused',
        'paused_from_status' => $status->status,
        'time_remaining_seconds' => $timeRemaining,
      ]);

      $pauseResultCaptured = false;

      for ($attempt = 0; $attempt < 10; $attempt++) {
        $latestResult = ExamPauseResult::where('exam_id', $exam->id)
          ->where('exam_step_id', $status->exam_step_id)
          ->where('participant_id', $participant->id)
          ->first();

        if ($latestResult && (!$previousUpdatedAt || $latestResult->updated_at->gt($previousUpdatedAt))) {
          $pauseResultCaptured = true;
          break;
        }

        usleep(200000);
      }

      $message = $pauseResultCaptured
        ? 'Teilnehmer wurde pausiert und Fortschritt gespeichert.'
        : 'Teilnehmer wurde pausiert. Fortschritt wird synchronisiert.';

      return back(303)->with('success', $message);
    }

    if ($status->status !== 'paused') {
      return back(303)->with('error', 'Teilnehmer ist aktuell nicht pausiert.');
    }

    $resumeStatus = $status->paused_from_status ?: 'not_started';
    $timeRemaining = max(0, (int) ($status->time_remaining_seconds ?? $totalDurationSeconds));
    $updates = [
      'paused_from_status' => null,
      'time_remaining_seconds' => null,
    ];

    if ($resumeStatus === 'in_progress') {
      $elapsed = max(0, $totalDurationSeconds - $timeRemaining);
      $updates['status'] = 'in_progress';
      $updates['started_at'] = $totalDurationSeconds > 0 ? now()->subSeconds($elapsed) : now();
    } elseif ($resumeStatus === 'completed') {
      $updates['status'] = 'completed';
    } else {
      $updates['status'] = 'not_started';
      $updates['started_at'] = null;
      $updates['completed_at'] = null;
    }

    $status->update($updates);

    return back(303)->with('success', 'Teilnehmer wurde fortgesetzt.');
  }

  public function updateSteps(Request $request, Exam $exam): RedirectResponse
  {
    $data = $request->validate([
      'steps' => 'required|array',
      'steps.*.id' => 'required|exists:tests,id'
    ]);

    // Delete existing steps
    $exam->steps()->delete();

    // Create new steps from request
    foreach ($data['steps'] as $index => $stepData) {
      $test = Test::find($stepData['id']);
      if ($test) {
        ExamStep::create([
          'exam_id' => $exam->id,
          'test_id' => $test->id,
          'step_order' => $index + 1,
          'duration' => $test->duration ?? 60, // Default duration
        ]);
      }
    }
    return redirect()->route('dashboard')->with('success', 'Exam steps updated successfully!');
  }
}
