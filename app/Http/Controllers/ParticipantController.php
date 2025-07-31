<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParticipantProfile;
use App\Models\ProfessionGroup;
use App\Models\Employed;

class ParticipantController extends Controller
{
  // Show the form
  public function showProfileForm()
  {
    $profile = Auth::user()?->participantProfile;
    $professionGroups = ProfessionGroup::all();
    $employeds = Employed::all();

    return inertia('Participant', [
      'profile' => $profile,
      'professionGroups' => $professionGroups,
      'employeds' => $employeds,
    ]);
  }

  // Save form
  public function storeProfile(Request $request)
  {
    $user = Auth::user();

    $data = $request->validate([
      'firstname'            => 'required|string|max:255',
      'name'                 => 'required|string|max:255',
      'birthday'             => 'required|date',
      'sex'                  => 'required|string|max:255',
      'education'            => 'nullable|string|max:255',
      'profession'           => 'nullable|string|max:255',
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
}
