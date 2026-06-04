<?php

namespace App\Http\Controllers;

use App\Models\ExamParticipant;
use App\Models\TestAssignment;
use App\Models\TestResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Inertia;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

class ResultPdfExportController extends Controller
{
  private const TEST_PDF_ORDER = [
    ['LPS-B'],
    ['BRT-A'],
    ['BRT-B'],
    ['MRT-A'],
    ['MRT-B'],
    ['BT'],
    ['FPI-R'],
    ['LMT'],
    ['BIT-2'],
    ['628', '628 TEST', 'KONZENTRATIONSTEST', '628 08.03'],
    ['AVEM'],
  ];

  public function showTestResult(Request $request, TestResult $testResult)
  {
    $testResult->load([
      'assignment.test',
      'assignment.participant.participantProfile',
      'teacher',
      'manualScores',
    ]);

    $participant = $testResult->assignment?->participant;
    abort_unless($participant, 404);

    $this->authorizeResultExport($request, $participant);

    $assignment = $testResult->assignment;
    $assignment->setRelation('results', new EloquentCollection([$testResult]));

    return Inertia::render('Print/TestResults', [
      'participant' => $participant,
      'assignments' => [$assignment],
      'autoPrint' => $request->boolean('auto_print'),
      'filename' => $this->filename($participant->name, '_' . ($assignment->test?->name ?? 'Test') . '_Ergebnis.pdf'),
      'pdfUrl' => URL::route('test-results.pdf', ['testResult' => $testResult->id], false),
    ]);
  }

  public function downloadTestResult(Request $request, TestResult $testResult)
  {
    $testResult->load(['assignment.test', 'assignment.participant']);
    $participant = $testResult->assignment?->participant;
    abort_unless($participant, 404);

    $this->authorizeResultExport($request, $participant);

    $filename = $this->filename($participant->name, '_' . ($testResult->assignment?->test?->name ?? 'Test') . '_Ergebnis.pdf');
    $printUrl = $this->signedPrintUrl($request, 'test-results.print-signed', ['testResult' => $testResult->id]);

    return $this->pdfResponse($request, $printUrl, $filename);
  }

  public function showParticipantResults(Request $request, User $participant)
  {
    $this->authorizeResultExport($request, $participant);

    $participant->load('participantProfile');

    return Inertia::render('Print/TestResults', [
      'participant' => $participant,
      'assignments' => $this->orderedAssignments($participant),
      'autoPrint' => $request->boolean('auto_print'),
      'filename' => $this->filename($participant->name, '_Alle_Tests.pdf'),
      'pdfUrl' => URL::route('participants.results.pdf', ['participant' => $participant->id], false),
    ]);
  }

  public function downloadParticipantResults(Request $request, User $participant)
  {
    $this->authorizeResultExport($request, $participant);

    $filename = $this->filename($participant->name, '_Alle_Tests.pdf');
    $printUrl = $this->signedPrintUrl($request, 'participants.results.print-signed', ['participant' => $participant->id]);

    return $this->pdfResponse($request, $printUrl, $filename);
  }

  private function pdfResponse(Request $request, string $printUrl, string $filename)
  {
    try {
      $pdf = $this->chromeCliPdf($printUrl);
    } catch (Throwable $chromeException) {
      try {
        $pdf = $this->browsershot($request, $printUrl)->pdf();
      } catch (Throwable $browsershotException) {
        report($chromeException);
        report($browsershotException);

        return response('Direct PDF generation is not available.', 503)
          ->header('X-Pdf-Print-Url', $printUrl);
      }
    }

    return response($pdf, 200, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
  }

  private function browsershot(Request $request, string $url)
  {
    if (!class_exists(\Spatie\Browsershot\Browsershot::class)) {
      throw new RuntimeException('Browsershot is not installed.');
    }

    $host = parse_url($url, PHP_URL_HOST);

    $browsershot = \Spatie\Browsershot\Browsershot::url($url)
      ->useCookies($request->cookies->all(), $host)
      ->windowSize(1280, 900)
      ->deviceScaleFactor(3)
      ->showBackground()
      ->emulateMedia('print')
      ->format('A4')
      ->margins(0, 0, 0, 0)
      ->waitUntilNetworkIdle(false)
      ->waitForFunction('window.__PDF_READY__ === true', null, 30000)
      ->timeout(120);

    if ($chromePath = config('services.browsershot.chrome_path')) {
      $browsershot->setChromePath($chromePath);
    }

    if ($nodeBinary = config('services.browsershot.node_binary')) {
      $browsershot->setNodeBinary($nodeBinary);
    }

    if ($npmBinary = config('services.browsershot.npm_binary')) {
      $browsershot->setNpmBinary($npmBinary);
    }

    if ($nodeModulePath = config('services.browsershot.node_module_path')) {
      $browsershot->setNodeModulePath($nodeModulePath);
    }

    if (config('services.browsershot.no_sandbox')) {
      $browsershot->noSandbox();
    }

    return $browsershot;
  }

  private function chromeCliPdf(string $url): string
  {
    $chromePath = $this->chromeExecutable();
    $workRoot = storage_path('app/pdf-export');
    $profileDir = $workRoot . DIRECTORY_SEPARATOR . 'chrome-profile-' . Str::uuid();
    $pdfPath = $workRoot . DIRECTORY_SEPARATOR . 'result-' . Str::uuid() . '.pdf';

    File::ensureDirectoryExists($workRoot);
    File::ensureDirectoryExists($profileDir);

    $arguments = [
      $chromePath,
      '--headless=new',
      '--disable-gpu',
      '--disable-dev-shm-usage',
      '--ignore-certificate-errors',
      '--no-first-run',
      '--no-default-browser-check',
      '--run-all-compositor-stages-before-draw',
      '--print-to-pdf=' . $pdfPath,
      '--print-to-pdf-no-header',
      '--virtual-time-budget=10000',
      '--window-size=1280,900',
      '--user-data-dir=' . $profileDir,
      $url,
    ];

    if (config('services.browsershot.no_sandbox')) {
      array_splice($arguments, 1, 0, ['--no-sandbox']);
    }

    try {
      $process = new Process($arguments, base_path());
      $process->setTimeout(120);
      $process->run();

      if (!$process->isSuccessful() || !File::exists($pdfPath) || File::size($pdfPath) === 0) {
        throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'Chrome PDF export failed.');
      }

      return File::get($pdfPath);
    } finally {
      File::delete($pdfPath);
      File::deleteDirectory($profileDir);
    }
  }

  private function chromeExecutable(): string
  {
    if ($configuredPath = config('services.browsershot.chrome_path')) {
      return $configuredPath;
    }

    $candidates = [
      'C:\Program Files\Google\Chrome\Application\chrome.exe',
      'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
      'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
      'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
      '/usr/bin/google-chrome',
      '/usr/bin/google-chrome-stable',
      '/usr/bin/chromium',
      '/usr/bin/chromium-browser',
      '/snap/bin/chromium',
    ];

    foreach ($candidates as $candidate) {
      if (File::exists($candidate)) {
        return $candidate;
      }
    }

    $finder = new ExecutableFinder();
    foreach (['google-chrome', 'google-chrome-stable', 'chromium', 'chromium-browser', 'microsoft-edge', 'msedge', 'chrome'] as $binary) {
      if ($path = $finder->find($binary)) {
        return $path;
      }
    }

    throw new RuntimeException('No Chrome or Edge executable was found for PDF export.');
  }

  private function orderedAssignments(User $participant): Collection
  {
    $allowedTestIds = ExamParticipant::where('participant_id', $participant->id)
      ->join('exam_steps', 'exam_participants.exam_id', '=', 'exam_steps.exam_id')
      ->pluck('exam_steps.test_id')
      ->filter()
      ->unique()
      ->values();

    if ($allowedTestIds->isEmpty()) {
      return collect();
    }

    return TestAssignment::query()
      ->where('participant_id', $participant->id)
      ->whereIn('test_id', $allowedTestIds)
      ->whereHas('results')
      ->with([
        'test',
        'results' => fn($query) => $query->with(['teacher', 'manualScores'])->orderByDesc('created_at'),
      ])
      ->get()
      ->map(fn(TestAssignment $assignment) => [
        'assignment' => $assignment,
        'order' => $this->testOrderIndex($assignment),
      ])
      ->filter(fn(array $entry) => $entry['order'] !== -1)
      ->sortBy('order')
      ->pluck('assignment')
      ->values();
  }

  private function testOrderIndex(TestAssignment $assignment): int
  {
    $identifiers = collect([$assignment->test?->name, $assignment->test?->code])
      ->filter()
      ->map(fn($value) => Str::upper(trim((string) $value)));

    foreach (self::TEST_PDF_ORDER as $index => $aliases) {
      foreach ($aliases as $alias) {
        if ($identifiers->contains(Str::upper($alias))) {
          return $index;
        }
      }
    }

    return -1;
  }

  private function authorizeResultExport(Request $request, User $participant): void
  {
    abort_unless($participant->role === 'participant', 404);

    if (Str::endsWith((string) $request->route()?->getName(), '.print-signed')) {
      return;
    }

    $user = $request->user();

    abort_unless(in_array($user?->role, ['admin', 'teacher'], true), 403);

    if ($user->role === 'teacher') {
      abort_unless($participant->city_id === $user->city_id, 403);
    }
  }

  private function signedPrintUrl(Request $request, string $name, array $parameters): string
  {
    $relativeUrl = URL::temporarySignedRoute($name, now()->addMinutes(5), $parameters, false);

    return $request->getSchemeAndHttpHost() . $relativeUrl;
  }

  private function filename(?string $name, string $suffix): string
  {
    $safeName = Str::ascii((string) (($name ?: 'Teilnehmer') . $suffix));
    $safeName = preg_replace('/[\\\\\/:*?"<>|]+/', '-', $safeName) ?: 'Teilnehmer.pdf';
    $safeName = preg_replace('/\s+/', '_', $safeName) ?: 'Teilnehmer.pdf';
    $safeName = trim($safeName, '._-');

    return $safeName ?: 'Teilnehmer.pdf';
  }
}
