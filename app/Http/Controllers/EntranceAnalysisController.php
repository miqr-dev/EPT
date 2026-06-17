<?php

namespace App\Http\Controllers;

use App\Models\EntranceAnalysis;
use App\Models\User;
use Illuminate\Http\Request;

class EntranceAnalysisController extends Controller
{
    public function update(Request $request, User $participant)
    {
        $this->authorizeTeacherAccess($request, $participant);

        $data = $request->validate([
            'instruction_understanding' => ['nullable', 'string', 'max:1000'],
            'work_method' => ['nullable', 'string', 'max:1000'],
            'work_speed' => ['nullable', 'string', 'max:1000'],
            'group_behavior' => ['nullable', 'string', 'max:1000'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        EntranceAnalysis::updateOrCreate(
            ['participant_id' => $participant->id],
            [
                ...$data,
                'teacher_id' => $request->user()->id,
            ],
        );

        return back(303);
    }

    private function authorizeTeacherAccess(Request $request, User $participant): void
    {
        abort_unless($participant->role === 'participant', 404);

        $user = $request->user();
        abort_unless(in_array($user?->role, ['admin', 'teacher'], true), 403);

        if ($user->role === 'teacher') {
            abort_unless($participant->city_id === $user->city_id, 403);
        }
    }
}
