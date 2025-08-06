<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParticipantProfile;
use App\Models\ProfessionGroup;
use App\Models\Employed;
use App\Models\ExamParticipant;
use App\Models\ExamStepStatus;
use Inertia\Inertia;

class ParticipantController extends Controller
{
  // Show the form
  public function showProfileForm()
  {
    // $profile = Auth::user()?->participantProfile;
    $professionGroups = ProfessionGroup::all();
    $employeds = Employed::all();

    return inertia('Participant', [
      // 'profile' => $profile,
      'user' => Auth::user(),
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

    return redirect()->route('my-exam');
  }

  public function examLauncher()
  {
    $user = Auth::user();
    $examParticipant = ExamParticipant::where('participant_id', $user->id)->first();

    if (!$examParticipant) {
        return redirect()->route('participant.onboarding')->with('error', 'You are not yet assigned to an exam. Please wait for an administrator to assign you.');
    }

    return Inertia::render('Exams/ExamLauncher', [
        'examUrl' => route('exam-room', ['exam' => $examParticipant->exam_id]),
    ]);
  }

  public function noExam()
  {
    return Inertia::render('Exams/NoExam');
  }

    public function myExam($examId)
    {
        $user = Auth::user();

        $exam = Exam::with(['currentStep.test'])->findOrFail($examId);

        // Ensure the participant is assigned to this exam
        $isParticipant = $exam->participants()->where('participant_id', $user->id)->exists();
        if (!$isParticipant) {
            abort(403, 'You are not assigned to this exam.');
        }

        $myStepStatus = null;
        if ($exam->currentStep) {
            $myStepStatus = ExamStepStatus::where('exam_step_id', $exam->currentStep->id)
                ->where('participant_id', $user->id)
                ->first();
        }

        return Inertia::render('Exams/ExamRoom', [
            'exam' => $exam,
            'myStepStatus' => $myStepStatus,
        ]);
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
}
