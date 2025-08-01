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

    return redirect()->route('dashboard'); // or any route you want
  }

    public function myExam()
    {
        $user = Auth::user();
        $examParticipant = ExamParticipant::where('participant_id', $user->id)->first();

        if (!$examParticipant) {
            return redirect()->route('dashboard')->with('error', 'You are not assigned to any exam.');
        }

        $exam = $examParticipant->exam()->with(['currentStep.test'])->first();

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
