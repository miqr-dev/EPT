<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParticipantProfile;
use App\Models\ProfessionGroup;
use App\Models\Employed;
use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamStepStatus;
use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

class ParticipantController extends Controller
{
  // Show the form
  public function showProfileForm()
  {
    $user = Auth::user()->load('participantProfile');
    $professionGroups = ProfessionGroup::all();
    $employeds = Employed::all();

    // Fetch MRT-A results
    $mrtTest = \App\Models\Test::where('name', 'MRT-A')->first();
    $mrtResult = null;
    if ($mrtTest) {
      $assignment = TestAssignment::where('participant_id', $user->id)
        ->where('test_id', $mrtTest->id)
        ->first();

      if ($assignment) {
        $mrtResult = TestResult::where('assignment_id', $assignment->id)
          ->latest()
          ->first();
      }
    }

    return inertia('Participant', [
      'user' => $user,
      'profile' => $user->participantProfile,
      'professionGroups' => $professionGroups,
      'employeds' => $employeds,
      'mrtResult' => $mrtResult ? $mrtResult->result_json : null,
    ]);
  }

  // Save form
  public function storeProfile(Request $request)
  {
    $user = Auth::user();

    $data = $request->validate([
      'birthday'             => 'required|date',
      'sex'                  => 'required|string|max:255',
      'employed_id'          => 'nullable|exists:employeds,id',
      'profession_group_id'  => 'nullable|exists:profession_groups,id',
    ]);

    $data['age'] = \Carbon\Carbon::parse($data['birthday'])->age;

    foreach (['education', 'marital_status', 'household', 'employed_id', 'profession_group_id'] as $optionalField) {
      if (array_key_exists($optionalField, $data) && $data[$optionalField] === '') {
        $data[$optionalField] = null;
      }
    }

    $profile = $user->participantProfile ?: new ParticipantProfile(['user_id' => $user->id]);
    $profile->fill($data)->save();

    $examParticipant = ExamParticipant::latestForParticipant($user->id);
    if ($examParticipant) {
      $exam = Exam::find($examParticipant->exam_id);
      if ($exam && in_array($exam->status, ['in_progress', 'paused', 'completed'], true)) {
        return redirect()->route('my-exam');
      }
    }
    return redirect()->route('participant.no-exam');
    }

  public function examLauncher()
  {
    $user = Auth::user();
    $examParticipant = ExamParticipant::latestForParticipant($user->id);

    if (!$examParticipant) {
      // Redirect to onboarding if not yet assigned to an exam.
      return redirect()->route('participant.onboarding')->with('error', 'You are not yet assigned to an exam. Please wait for an administrator to assign you.');
    }

    $exam = Exam::with(['steps.test', 'currentStep.test'])->find($examParticipant->exam_id);

    if (!$exam || !in_array($exam->status, ['in_progress', 'paused', 'completed'], true)) {
      // Either exam does not exist or hasn't started yet.
      return redirect()->route('participant.no-exam');
    }

    // Eager load all step statuses for this participant in this exam.
    $stepStatuses = ExamStepStatus::where('exam_id', $exam->id)
      ->where('participant_id', $user->id)
      ->with('step')
      ->get()
      ->keyBy('exam_step_id');

    $completedTestIds = TestAssignment::where('participant_id', $user->id)
      ->where('status', 'completed')
      ->whereHas('results')
      ->pluck('test_id')
      ->unique();

    // Ensure step statuses exist for all steps, creating them if necessary.
    foreach ($exam->steps as $step) {
      if (!isset($stepStatuses[$step->id])) {
        $isCompleted = $step->test_id && $completedTestIds->contains($step->test_id);
        $newStatus = ExamStepStatus::create([
          'exam_id' => $exam->id,
          'exam_step_id' => $step->id,
          'participant_id' => $user->id,
          'status' => $isCompleted ? 'completed' : 'not_started',
          'completed_at' => $isCompleted ? now() : null,
        ]);
        $stepStatuses[$step->id] = $newStatus;
      } else {
        $status = $stepStatuses[$step->id];
        if ($status->status === 'not_started' && $step->test_id && $completedTestIds->contains($step->test_id)) {
          $status->update([
            'status' => 'completed',
            'completed_at' => $status->completed_at ?? now(),
          ]);
          $stepStatuses[$step->id] = $status->fresh();
        }
      }
    }

    foreach ($stepStatuses as $status) {
      $durationMinutes = $status->step->duration ?? $exam->steps->firstWhere('id', $status->exam_step_id)?->duration ?? 0;

      $totalDurationSeconds = max(0, (int) $durationMinutes * 60
        + (int) $status->extra_time * 60
        + (int) $status->grace_period_seconds);

      if ($status->status === 'paused') {
        $timeRemaining = max(0, (int) ($status->time_remaining_seconds ?? $totalDurationSeconds));
      } elseif ($status->status === 'not_started') {
        $timeRemaining = $totalDurationSeconds;
      } elseif ($status->started_at) {
        $startTime = Carbon::parse($status->started_at);
        $endTime = $startTime->copy()->addSeconds($totalDurationSeconds);
        $timeRemaining = $startTime ? now()->diffInSeconds($endTime, false) : $totalDurationSeconds;
      } else {
        $timeRemaining = $totalDurationSeconds;
      }

      $status->time_remaining_seconds = max(0, (int) $timeRemaining);
    }


    $pausedTestResults = [];

    $testIds = $exam->steps
      ->pluck('test_id')
      ->filter()
      ->unique();

    if ($testIds->isNotEmpty()) {
      $assignments = TestAssignment::where('participant_id', $user->id)
        ->whereIn('test_id', $testIds)
        ->with(['results' => function ($query) {
          $query->latest()->limit(1);
        }])
        ->get()
        ->keyBy('test_id');

      foreach ($exam->steps as $step) {
        $status = $stepStatuses[$step->id] ?? null;
        if (!$status) {
          continue;
        }

        if (!in_array($status->status, ['paused', 'in_progress'], true)) {
          continue;
        }

        $assignment = $assignments->get($step->test_id);
        if (!$assignment || $assignment->results->isEmpty()) {
          continue;
        }

        $latestResult = $assignment->results->first();
        if ($latestResult) {
          $pausedTestResults[$step->id] = $latestResult->result_json;
        }
      }
    }

    return Inertia::render('Exams/ExamRoom', [
      'exam' => $exam,
      'stepStatuses' => $stepStatuses, // Pass all statuses to the view.
      'pausedTestResults' => (object) $pausedTestResults,
    ]);
  }

  public function noExam()
  {
    return Inertia::render('Exams/NoExam');
  }

  public function startStep(Request $request)
  {
    $user = Auth::user();
    $examStepStatus = ExamStepStatus::where('participant_id', $user->id)
      ->where('exam_step_id', $request->input('exam_step_id'))
      ->firstOrFail();

    $examStepStatus->update([
      'status' => 'in_progress',
      'started_at' => now(),
    ]);

    return back(303);
  }

  public function completeStep(Request $request)
  {
    $user = Auth::user();
    $examStepStatus = ExamStepStatus::with('step.test')
      ->where('participant_id', $user->id)
      ->where('exam_step_id', $request->input('exam_step_id'))
      ->firstOrFail();

    $examStepStatus->update([
      'status' => 'completed',
      'completed_at' => now(),
      'force_finish_requested_at' => null,
      'force_finish_deadline' => null,
    ]);

    $results = $request->input('results');

    if ($results) {
      $examStep = $examStepStatus->step;
      if ($examStep && $examStep->test) {
        $assignment = TestAssignment::firstOrCreate(
          [
            'participant_id' => $user->id,
            'test_id' => $examStep->test->id,
          ],
          [
            'status' => 'assigned',
          ]
        );

        // Calculate score on backend for specific tests
        $resultData = $results;
        if ($examStep->test->name === 'BRT-A') {
          $answers = $results['answers'] ?? [];
          $times = $results['question_times'] ?? [];
          $resultData = \App\Services\BrtAScorer::score($answers, $times);
        } elseif ($examStep->test->name === 'BRT-B') {
          $answers = $results['answers'] ?? [];
          $times = $results['question_times'] ?? [];
          $resultData = \App\Services\BrtBScorer::score($answers, $times);
        } elseif ($examStep->test->name === 'FPI-R') {
          $answers = $results['answers'] ?? [];
          $totalTime = $results['total_time_seconds'] ?? null;
          $profile = $user->participant_profile;
          $sex = $profile->sex ?? null;
          $age = $profile->age ?? null;
          $resultData = \App\Services\FpiRScorer::score($answers, $sex, $age, $totalTime);
        } elseif ($examStep->test->name === 'MRT-A') {
          $userAnswers = array_column($results['answers'] ?? [], 'user_answer');
          $questionTimes = array_column($results['answers'] ?? [], 'time_seconds');
          $user->load('participantProfile');
          $profile = $user->participantProfile;
          $age = $profile->age ?? null;
          $resultData = \App\Services\MrtAScorer::score($userAnswers, $age, $questionTimes);
        } elseif ($examStep->test->name === 'BIT-2') {
          $answers = $results['answers'] ?? [];
          $user->load('participantProfile');
          $profile = $user->participantProfile;
          $sex = $profile->sex ?? null;
          $resultData = \App\Services\Bit2Scorer::score($answers, $sex);
        }

        $existingResult = TestResult::where('assignment_id', $assignment->id)->latest()->first();
        $exam = Exam::find($examStepStatus->exam_id);
        $teacherId = $exam ? $exam->teacher_id : null;

        if (!($assignment->status === 'completed' && $existingResult)) {
          TestResult::updateOrCreate(
            [
              'assignment_id' => $assignment->id,
            ],
            [
              'result_json' => $resultData,
              'teacher_id' => $teacherId,
            ]
          );

          $assignment->update([
            'status' => 'completed',
            'completed_at' => now(),
          ]);
        }
      }
    }

    return back(303);
  }

  public function breakStep(Request $request)
  {
    $user = Auth::user();
    $examStepStatus = ExamStepStatus::where('participant_id', $user->id)
      ->where('exam_step_id', $request->input('exam_step_id'))
      ->firstOrFail();

    $examStepStatus->update([
      'status' => 'broken',
      'completed_at' => now(),
      'force_finish_requested_at' => null,
      'force_finish_deadline' => null,
    ]);

    return back(303);
  }

  public function list(Request $request)
  {
    $user = Auth::user();
    $cityId = $user->city_id;
    $search = trim($request->input('search', ''));

    $participants = \App\Models\User::where('role', 'participant')
      ->where('city_id', $cityId)
      ->when($search !== '', function ($query) use ($search) {
        $query->where(function ($subQuery) use ($search) {
          $subQuery->where('name', 'like', "%{$search}%")
            ->orWhere('firstname', 'like', "%{$search}%")
            ->orWhere('username', 'like', "%{$search}%");
        });
      })
      ->with([
        'participantProfile',
        'testAssignments.test',
        'testAssignments.results' => function ($query) {
          $query->with(['teacher', 'manualScores'])->orderBy('created_at', 'desc');
        },
        'tests',
      ])
      ->addSelect([
        'latest_exam_created_at' => Exam::select('exams.created_at')
          ->join('exam_participants', 'exams.id', '=', 'exam_participants.exam_id')
          ->whereColumn('exam_participants.participant_id', 'users.id')
          ->latest('exams.created_at')
          ->limit(1),
      ])
      ->orderByDesc('latest_exam_created_at')
      ->paginate(15)
      ->withQueryString();

    $examTestIdsByParticipant = ExamParticipant::whereIn('participant_id', $participants->pluck('id'))
      ->join('exam_steps', 'exam_participants.exam_id', '=', 'exam_steps.exam_id')
      ->select('exam_participants.participant_id', 'exam_steps.test_id')
      ->get()
      ->groupBy('participant_id')
      ->map(function ($rows) {
        return $rows->pluck('test_id')->filter()->unique();
      });

    $participants->getCollection()->transform(function ($participant) use ($examTestIdsByParticipant) {
      $allowedTestIds = $examTestIdsByParticipant->get($participant->id, collect());
      $filteredAssignments = $participant->testAssignments->whereIn('test_id', $allowedTestIds);
      $participant->setRelation('testAssignments', $filteredAssignments->values());
      $filteredTests = $participant->tests->whereIn('id', $allowedTestIds);
      $participant->setRelation('tests', $filteredTests->values());
      return $participant;
    });

    return Inertia::render('Participants/List', [
      'participants' => $participants,
      'filters' => [
        'search' => $search,
      ],
    ]);
  }


  public function import(Request $request)
  {
    $user = Auth::user();

    if (!in_array($user->role, ['admin', 'teacher'])) {
      abort(403);
    }

    $data = $request->validate([
      'username' => ['required', 'string', 'max:255'],
      'role' => ['required', 'in:participant,teacher'],
      'can_login' => ['nullable', 'boolean'],
    ]);

    $username = mb_strtolower(trim($data['username']));

    $participant = User::firstOrCreate(
      ['username' => $username],
      [
        'name' => $username,
        'firstname' => $username,
        'password' => bcrypt(str()->random(32)),
        'role' => $data['role'],
        'city_id' => $user->city_id,
        'can_login' => (bool) ($data['can_login'] ?? false),
      ]
    );

    if (!$participant->wasRecentlyCreated) {
      if ($user->role === 'teacher' && $participant->city_id !== $user->city_id) {
        abort(403);
      }

      $participant->forceFill([
        'role' => $data['role'],
        'can_login' => (bool) ($data['can_login'] ?? false),
      ])->save();
    }

    return back()->with('success', __('Benutzer wurde importiert und kann jetzt verwaltet werden.'));
  }

  public function updateLoginPermission(Request $request, User $participant)
  {
    $user = Auth::user();

    if (!in_array($user->role, ['admin', 'teacher'])) {
      abort(403);
    }

    if ($participant->role !== 'participant') {
      abort(404);
    }

    if ($user->role === 'teacher' && $participant->city_id !== $user->city_id) {
      abort(403);
    }

    $data = $request->validate([
      'can_login' => ['required', 'boolean'],
    ]);

    $participant->forceFill([
      'can_login' => $data['can_login'],
    ])->save();

    return back()->with('success', __('Anmeldeberechtigung aktualisiert.'));
  }

  public function setContractVisibility(Request $request, User $participant)
  {
    $user = Auth::user();

    if (!in_array($user->role, ['admin', 'teacher'], true)) {
      abort(403);
    }

    if ($participant->role !== 'participant') {
      abort(404);
    }

    if ($user->role === 'teacher' && $participant->city_id !== $user->city_id) {
      abort(403);
    }

    $data = $request->validate([
      'enabled' => ['required', 'boolean'],
    ]);

    $participant->timestamps = false;
    $participant->forceFill([
      'contract_view_enabled' => $data['enabled'],
    ])->save();
    $participant->timestamps = true;

    return back()->with('success', __('Vertragsansicht aktualisiert.'));
  }

  public function contractStatus(Request $request)
  {
    $user = Auth::user()->fresh();

    $examId = $request->integer('exam_id');
    $examContractEnabled = null;

    if ($examId) {
      $examParticipant = ExamParticipant::where('participant_id', $user->id)
        ->where('exam_id', $examId)
        ->first();

      if ($examParticipant) {
        $examContractEnabled = (bool) Exam::where('id', $examId)
          ->value('contract_view_enabled');
      }
    }

    return response()->json([
      'user_contract_view_enabled' => (bool) $user->contract_view_enabled,
      'exam_contract_view_enabled' => $examContractEnabled,
    ]);
  }

  public function pauseStep(Request $request)
  {
    $user = Auth::user();
    $examStepStatus = ExamStepStatus::with('step.test')
      ->where('participant_id', $user->id)
      ->where('exam_step_id', $request->input('exam_step_id'))
      ->firstOrFail();

    $results = $request->input('results');

    if ($results) {
      $examStep = $examStepStatus->step;
      if ($examStep && $examStep->test) {
        $assignment = TestAssignment::firstOrCreate(
          [
            'participant_id' => $user->id,
            'test_id' => $examStep->test->id,
          ],
          [
            'status' => 'assigned',
          ]
        );

        $existingResult = TestResult::where('assignment_id', $assignment->id)->latest()->first();
        $exam = Exam::find($examStepStatus->exam_id);
        $teacherId = $exam ? $exam->teacher_id : null;

        if (!($assignment->status === 'completed' && $existingResult)) {
          TestResult::updateOrCreate(
            [
              'assignment_id' => $assignment->id,
            ],
            [
              'result_json' => $results,
              'teacher_id' => $teacherId,
            ]
          );
        }
      }
    }

    return back(303);
  }
}
