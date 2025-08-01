<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\User;
use App\Models\Test;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

  // 4. Show exam details (with step/participant management)
  public function show(Exam $exam)
  {
    $exam->load(['city', 'teacher', 'participants.user', 'steps.test', 'currentStep']);
    $availableParticipants = User::where('role', 'participant')
      ->where('city_id', $exam->city_id)
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
  // ... (see previous post for methods, but always use Inertia::render or redirect)

  public function storeWithParticipants(Request $request)
  {
      $data = $request->validate([
          'title' => 'required|string|max:255',
          'participant_ids' => 'required|array|min:1',
          'participant_ids.*' => 'exists:users,id',
      ]);

      $teacher = Auth::user();

      DB::transaction(function () use ($data, $teacher) {
          $exam = Exam::create([
              'name' => $data['title'],
              'city_id' => $teacher->city_id,
              'teacher_id' => $teacher->id,
              'status' => 'not_started',
          ]);

          foreach ($data['participant_ids'] as $participantId) {
              ExamParticipant::create([
                  'exam_id' => $exam->id,
                  'participant_id' => $participantId,
              ]);
          }

          // Create default steps
          $defaultTestNames = ['LMT', 'LMT2', 'BRT', 'FPI', 'MRT'];
          $defaultTests = Test::whereIn('name', $defaultTestNames)->pluck('id', 'name');

          $stepIndex = 1;
          foreach ($defaultTestNames as $testName) {
              if (isset($defaultTests[$testName])) {
                  ExamStep::create([
                      'exam_id' => $exam->id,
                      'test_id' => $defaultTests[$testName],
                      'step_order' => $stepIndex++,
                      'duration' => 60, // default duration
                  ]);
              }
          }
      });

      return redirect()->route('dashboard')->with('success', 'Exam created successfully!');
  }

  public function updateSteps(Request $request, Exam $exam)
  {
      $data = $request->validate([
          'steps' => 'required|array',
          'steps.*.id' => 'required|exists:exam_steps,id',
      ]);

      DB::transaction(function () use ($data, $exam) {
          foreach ($data['steps'] as $index => $stepData) {
              $step = ExamStep::where('exam_id', $exam->id)->findOrFail($stepData['id']);
              $step->update(['step_order' => $index + 1]);
          }
      });

      return redirect()->route('dashboard')->with('success', 'Exam steps updated successfully!');
  }
}
