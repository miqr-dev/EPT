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
            'mark_overrides' => ['nullable', 'array', 'max:500'],
            'mark_overrides.*' => ['boolean'],
            'value_overrides' => ['nullable', 'array', 'max:500'],
            'value_overrides.*' => ['nullable', 'numeric'],
        ]);

        $updates = [
            ...collect($data)->except('mark_overrides', 'value_overrides')->all(),
            'teacher_id' => $request->user()->id,
        ];

        if (array_key_exists('mark_overrides', $data)) {
            $updates['mark_overrides'] = collect($data['mark_overrides'] ?? [])
                ->map(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN))
                ->all();
        }

        if (array_key_exists('value_overrides', $data)) {
            $updates['value_overrides'] = collect($data['value_overrides'] ?? [])
                ->map(function ($value) {
                    if ($value === null) {
                        return null;
                    }

                    $number = (float) $value;

                    return floor($number) === $number ? (int) $number : $number;
                })
                ->all();
        }

        $analysis = EntranceAnalysis::updateOrCreate(
            ['participant_id' => $participant->id],
            $updates,
        );

        if ($request->expectsJson()) {
            return response()->json([
                'analysis' => $analysis->load('teacher'),
            ]);
        }

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
