<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Exam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminExamOverviewController extends Controller
{
  public function __invoke(Request $request): Response
  {
    abort_unless($request->user()?->role === 'admin', 403);

    $locations = City::query()
      ->with(['exams' => function ($query) {
        $query
          ->whereIn('status', ['not_started', 'in_progress', 'paused'])
          ->with([
            'teacher:id,name,firstname,city_id',
            'steps.test:id,name,code,duration',
            'currentStep.test:id,name,code,duration',
          ])
          ->withCount('participants')
          ->orderByRaw("CASE WHEN status IN ('in_progress', 'paused') THEN 0 ELSE 1 END")
          ->latest();
      }])
      ->orderBy('name')
      ->get()
      ->map(function (City $city) {
        $ongoingExams = $city->exams
          ->whereIn('status', ['in_progress', 'paused'])
          ->values()
          ->map(fn (Exam $exam) => $this->examPayload($exam))
          ->all();

        $plannedExams = $city->exams
          ->where('status', 'not_started')
          ->values()
          ->map(fn (Exam $exam) => $this->examPayload($exam))
          ->all();

        return [
          'id' => $city->id,
          'name' => $city->name,
          'ongoingExams' => $ongoingExams,
          'plannedExams' => $plannedExams,
        ];
      })
      ->values();

    return Inertia::render('Admin/ExamOverview', [
      'locations' => $locations,
      'summary' => [
        'locationCount' => $locations->count(),
        'ongoingCount' => $locations->sum(fn (array $location) => count($location['ongoingExams'])),
        'plannedCount' => $locations->sum(fn (array $location) => count($location['plannedExams'])),
      ],
    ]);
  }

  private function examPayload(Exam $exam): array
  {
    $teacherName = trim(implode(' ', array_filter([
      $exam->teacher?->name,
      $exam->teacher?->firstname,
    ])));

    return [
      'id' => $exam->id,
      'name' => $exam->name,
      'status' => $exam->status,
      'teacherName' => $teacherName !== '' ? $teacherName : null,
      'participantsCount' => $exam->participants_count,
      'currentStep' => $exam->currentStep ? [
        'id' => $exam->currentStep->id,
        'name' => $exam->currentStep->test?->name,
        'code' => $exam->currentStep->test?->code,
      ] : null,
      'steps' => $exam->steps
        ->map(fn ($step) => [
          'id' => $step->id,
          'name' => $step->test?->name,
          'code' => $step->test?->code,
          'duration' => $step->duration,
        ])
        ->values()
        ->all(),
      'createdAt' => $exam->created_at?->toIso8601String(),
      'startedAt' => $exam->started_at?->toIso8601String(),
    ];
  }
}
