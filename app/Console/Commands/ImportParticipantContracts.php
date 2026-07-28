<?php

namespace App\Console\Commands;

use App\Models\ParticipantProfile;
use App\Models\User;
use App\Services\Contracts\PdfContractDataExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportParticipantContracts extends Command
{
    protected $signature = 'participants:import-contracts
        {--report= : Optional storage-relative report path}
        {--dry-run : Parse and report only, do not persist data}
        {--scheduled : Internal flag used by scheduler for log context}
        {--participant_id= : Import only one participant by user id}
        {--trigger= : Optional trigger label for logs/reports}';

    protected $description = 'Import participant contract data from PDF files and fill missing profile fields.';

    public function handle(PdfContractDataExtractor $extractor): int
    {
        $disk = Storage::disk('pdfs');
        $isDryRun = (bool) $this->option('dry-run');
        $trigger = (string) ($this->option('trigger') ?: ($this->option('scheduled') ? 'scheduled' : 'manual'));
        $participantId = $this->option('participant_id');

        $report = [
            'checked' => 0,
            'imported' => 0,
            'skipped_existing_import' => 0,
            'skipped_missing_pdf' => 0,
            'skipped_missing_profile' => 0,
            'skipped_unparseable' => 0,
        ];

        $query = User::query()
            ->where('role', 'participant')
            ->whereNotNull('city_id')
            ->with(['city', 'participantProfile']);

        if ($participantId !== null && $participantId !== '') {
            $query->whereKey((int) $participantId);
        }

        $query->chunkById(100, function ($users) use ($disk, $extractor, $isDryRun, &$report) {
            foreach ($users as $user) {
                $report['checked']++;

                $cityName = Str::of((string) optional($user->city)->name)->trim()->replace(' ', '_');
                if ($cityName->isEmpty()) {
                    $report['skipped_missing_pdf']++;
                    continue;
                }

                $username = Str::of((string) $user->username)->trim();
                $pdfPath = collect([
                    $cityName . '/' . $username . '.pdf',
                    $cityName . '/' . $username . '.PDF',
                ])->first(fn ($path) => $disk->exists($path));

                if (!$pdfPath) {
                    $report['skipped_missing_pdf']++;
                    continue;
                }

                $parsed = $extractor->extract($disk->get($pdfPath));
                if ($parsed === []) {
                    $report['skipped_unparseable']++;
                    Log::warning('Participant contract import could not parse pdf', [
                        'participant_id' => $user->id,
                        'pdf_path' => $pdfPath,
                    ]);
                    $this->appendImportLog('unparseable', [
                        'participant_id' => $user->id,
                        'pdf_path' => $pdfPath,
                    ]);
                    continue;
                }

                $profile = $user->participantProfile;

                if ($profile && $profile->contract_imported_at !== null) {
                    $report['skipped_existing_import']++;
                    continue;
                }

                if (!$profile) {
                    $profile = new ParticipantProfile([
                        'user_id' => $user->id,
                    ]);
                }

                $this->fillIfMissing($user, $profile, $parsed);

                if (empty($profile->sex)) {
                    $profile->sex = (string) ($parsed['sex'] ?? 'unbekannt');
                }

                if (empty($profile->birthday)) {
                    $report['skipped_missing_profile']++;
                    $this->appendImportLog('missing_required_birthday', [
                        'participant_id' => $user->id,
                        'pdf_path' => $pdfPath,
                    ]);
                    continue;
                }

                if (empty($profile->age) && !empty($profile->birthday)) {
                    $profile->age = now()->diffInYears($profile->birthday);
                }

                if (!$isDryRun) {
                    $profile->contract_imported_at = now();
                    $profile->save();
                }

                $report['imported']++;
            }
        });

        $reportPath = $this->option('report') ?: 'reports/contracts-import-' . now()->format('Ymd_His') . '.json';
        $payload = [
            'ran_at' => now()->toIso8601String(),
            'timezone' => config('app.timezone'),
            'trigger' => $trigger,
            'dry_run' => $isDryRun,
            'participant_id' => $participantId !== null && $participantId !== '' ? (int) $participantId : null,
            'summary' => $report,
        ];

        Storage::disk('local')->put($reportPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Log::info('Participant contract import finished', [
            'report_path' => $reportPath,
            ...$payload,
        ]);

        $this->appendImportLog('finished', [
            'report_path' => $reportPath,
            ...$payload,
        ]);

        $this->info('Contract import done: ' . json_encode($report));
        $this->info('Trigger: ' . $trigger . ', timezone: ' . config('app.timezone') . ', dry-run: ' . ($isDryRun ? 'yes' : 'no'));
        $this->info('Report written to storage/app/private/' . $reportPath);
        $this->info('Log written to storage/logs/contracts-import.log (always) and default app log channel.');

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $parsed
     */
    private function fillIfMissing(User $user, ParticipantProfile $profile, array $parsed): void
    {
        $userMap = [
            'firstname' => 'firstname',
            'name' => 'name',
        ];

        foreach ($userMap as $from => $to) {
            if (!empty($parsed[$from]) && empty($user->{$to})) {
                $user->{$to} = $parsed[$from];
            }
        }

        if ($user->isDirty()) {
            $user->save();
        }

        $profileMap = [
            'birthday' => 'birthday',
            'course' => 'course',
            'location' => 'location',
            'measure_start' => 'measure_start',
            'measure_end' => 'measure_end',
            'measure_time' => 'measure_time',
            'street' => 'street',
            'zip' => 'zip',
            'city_name' => 'city_name',
            'telephone' => 'telephone',
            'cost_bearer' => 'cost_bearer',
        ];

        foreach ($profileMap as $from => $to) {
            if (!empty($parsed[$from]) && empty($profile->{$to})) {
                $profile->{$to} = $parsed[$from];
            }
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function appendImportLog(string $event, array $context): void
    {
        $line = '[' . now()->toDateTimeString() . '] ' . $event . ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        @file_put_contents(storage_path('logs/contracts-import.log'), $line, FILE_APPEND);
    }
}
