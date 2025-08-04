<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\ExamStepStatus;
use App\Models\User;
use App\Models\Test;
use App\Models\City;
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

        $firstStep = $exam->steps()->orderBy('step_order')->first();

        if (!$firstStep) {
            return back(303)->with('error', 'Exam has no steps.');
        }

        $exam->update([
            'status' => 'in_progress',
            'current_exam_step_id' => $firstStep->id,
            'started_at' => now(),
        ]);

        // Create status records for all participants for the first step
        foreach ($exam->participants as $participant) {
            ExamStepStatus::create([
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
            foreach ($exam->participants as $participant) {
                ExamStepStatus::create([
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
}
