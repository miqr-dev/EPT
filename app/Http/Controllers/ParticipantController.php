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
use Inertia\Inertia;

class ParticipantController extends Controller
{
  // Show the form
  public function showProfileForm()
  {
    $user = Auth::user()->load('participantProfile');
    $professionGroups = ProfessionGroup::all();
    $employeds = Employed::all();

    return inertia('Participant', [
      'user' => $user,
      'profile' => $user->participantProfile,
      'professionGroups' => $professionGroups,
      'employeds' => $employeds,
    ]);
  }

  // Save form
  public function storeProfile(Request $request)
  {
    $user = Auth::user();

    $data = $request->validate([
      'birthday'             => 'required|date',
      'sex'                  => 'required|string|max:255',
      'education'            => 'nullable|string|max:255',
      'marital_status'       => 'required|string|max:255',
      'household'            => 'required|string|max:255',
      'employed_id'          => 'nullable|exists:employeds,id',
      'profession_group_id'  => 'nullable|exists:profession_groups,id',
    ]);

    $data['age'] = \Carbon\Carbon::parse($data['birthday'])->age;

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


    return Inertia::render('Exams/ExamRoom', [
      'exam' => $exam,
      'stepStatuses' => $stepStatuses, // Pass all statuses to the view.
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
    $examStepStatus = ExamStepStatus::where('participant_id', $user->id)
      ->where('exam_step_id', $request->input('exam_step_id'))
      ->firstOrFail();

    $examStepStatus->update([
      'status' => 'completed',
      'completed_at' => now(),
    ]);

    return back(303);
  }

  public function breakStep(Request $request)
  {
    $user = Auth::user();
    $examStepStatus = ExamStepStatus::where('participant_id', $user->id)
      ->where('exam_step_id', $request->input('exam_step_id'))
      ->firstOrFail();

    $examStepStatus->update([
      'status' => 'uncompleted',
      'completed_at' => now(),
    ]);

    return back(303);
  }
}
