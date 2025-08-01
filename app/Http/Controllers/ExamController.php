<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStep;
use App\Models\User;
use App\Models\Test;
use App\Models\City;
use Illuminate\Http\Request;
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
}
