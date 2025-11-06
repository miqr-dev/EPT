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

    $examParticipant = ExamParticipant::where('participant_id', $user->id)->first();
    if ($examParticipant) {
      $exam = Exam::find($examParticipant->exam_id);
      if ($exam && $exam->status === 'in_progress') {
        return redirect()->route('my-exam');
      }
    }
    return redirect()->route('participant.no-exam');
  }

  public function examLauncher()
  {
    $user = Auth::user();
    $examParticipant = ExamParticipant::where('participant_id', $user->id)->first();

    if (!$examParticipant) {
      // Redirect to onboarding if not yet assigned to an exam.
      return redirect()->route('participant.onboarding')->with('error', 'You are not yet assigned to an exam. Please wait for an administrator to assign you.');
    }

    $exam = Exam::with(['steps.test', 'currentStep.test'])->find($examParticipant->exam_id);

    if (!$exam || $exam->status !== 'in_progress') {
      // Either exam does not exist or hasn't started yet.
      return redirect()->route('participant.no-exam');
    }

    // Eager load all step statuses for this participant in this exam.
    $stepStatuses = ExamStepStatus::where('exam_id', $exam->id)
      ->where('participant_id', $user->id)
      ->get()
      ->keyBy('exam_step_id');

    // Ensure step statuses exist for all steps, creating them if necessary.
    foreach ($exam->steps as $step) {
      if (!isset($stepStatuses[$step->id])) {
        $newStatus = ExamStepStatus::create([
          'exam_id' => $exam->id,
          'exam_step_id' => $step->id,
          'participant_id' => $user->id,
          'status' => 'not_started',
        ]);
        $stepStatuses[$step->id] = $newStatus;
      }
    }


    $pausedTestResult = null;
    if ($exam->current_exam_step_id) {
        $currentStepStatus = $stepStatuses[$exam->current_exam_step_id] ?? null;
        if ($currentStepStatus && $currentStepStatus->status === 'paused') {
            $assignment = TestAssignment::where('participant_id', $user->id)
                ->where('test_id', $exam->currentStep->test_id)
                ->first();

            if ($assignment) {
                $pausedTestResult = TestResult::where('assignment_id', $assignment->id)
                    ->where('is_paused', true)
                    ->latest()
                    ->first();
            }
        }
    }

    return Inertia::render('Exams/ExamRoom', [
      'exam' => $exam,
      'stepStatuses' => $stepStatuses, // Pass all statuses to the view.
      'pausedTestResult' => $pausedTestResult,
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

        $exam = Exam::find($examStepStatus->exam_id);
        $teacherId = $exam ? $exam->teacher_id : null;

        $testResult = TestResult::create([
          'assignment_id' => $assignment->id,
          'result_json' => $resultData,
          'teacher_id' => $teacherId,
        ]);

        // Clean up any temporary paused data
        TestResult::where('assignment_id', $assignment->id)
            ->where('is_paused', true)
            ->delete();

        $assignment->update([
          'status' => 'completed',
          'completed_at' => now(),
        ]);
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

  public function list()
  {
    $user = Auth::user();
    $cityId = $user->city_id;

    $participants = \App\Models\User::where('role', 'participant')
      ->where('city_id', $cityId)
      ->with([
        'participantProfile',
        'testAssignments.test',
        'testAssignments.results' => function ($query) {
          $query->with('teacher')->orderBy('created_at', 'desc');
        },
        'tests',
      ])
      ->get();

    $examTestIdsByParticipant = ExamParticipant::whereIn('participant_id', $participants->pluck('id'))
      ->join('exam_steps', 'exam_participants.exam_id', '=', 'exam_steps.exam_id')
      ->select('exam_participants.participant_id', 'exam_steps.test_id')
      ->get()
      ->groupBy('participant_id')
      ->map(function ($rows) {
        return $rows->pluck('test_id')->filter()->unique();
      });

    $participants->transform(function ($participant) use ($examTestIdsByParticipant) {
      $allowedTestIds = $examTestIdsByParticipant->get($participant->id, collect());
      $filteredAssignments = $participant->testAssignments->whereIn('test_id', $allowedTestIds);
      $participant->setRelation('testAssignments', $filteredAssignments->values());
      $filteredTests = $participant->tests->whereIn('id', $allowedTestIds);
      $participant->setRelation('tests', $filteredTests->values());
      return $participant;
    });

    return Inertia::render('Participants/List', [
      'participants' => $participants,
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

        $exam = Exam::find($examStepStatus->exam_id);
        $teacherId = $exam ? $exam->teacher_id : null;

        TestResult::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'is_paused' => true,
            ],
            [
                'result_json' => $results,
                'teacher_id' => $teacherId,
            ]
        );
      }
    }

    return back(303);
  }
}
