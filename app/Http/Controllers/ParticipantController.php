<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ParticipantProfile;
use App\Models\ProfessionGroup;

class ParticipantController extends Controller
{
  // Show the form
  public function showProfileForm()
  {
    $profile = Auth::user()->participantProfile;
    $professionGroups = ProfessionGroup::all();

    return inertia('Participant', [
      'profile' => $profile,
      'professionGroups' => $professionGroups,
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
      'sex'                  => 'required|in:m,f,d',
      'education'            => 'nullable|string|max:255',
      'profession'           => 'nullable|string|max:255',
      'marital_status'       => 'required|in:single,married,divorced/separated,widowed',
      'household'            => 'required|in:single,with_partner',
      'employed'             => 'required|boolean',
      'profession_group_id'  => 'nullable|exists:profession_groups,id',
    ]);

    $data['age'] = \Carbon\Carbon::parse($data['birthday'])->age;

    $profile = $user->participantProfile ?: new ParticipantProfile(['user_id' => $user->id]);
    $profile->fill($data)->save();

    return redirect()->route('dashboard'); // or any route you want
  }
}
