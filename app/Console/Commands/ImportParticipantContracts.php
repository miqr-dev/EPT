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
        {--scheduled : Internal flag used by scheduler for log context}';

    protected $description = 'Import participant contract data from PDF files and fill missing profile fields.';

    public function handle(PdfContractDataExtractor $extractor): int
    {
        $disk = Storage::disk('pdfs');
        $isDryRun = (bool) $this->option('dry-run');
        $trigger = $this->option('scheduled') ? 'scheduled' : 'manual';

        $report = [
            'checked' => 0,
            'imported' => 0,
            'skipped_existing_import' => 0,
            'skipped_missing_pdf' => 0,
            'skipped_unparseable' => 0,
        ];

        User::query()
            ->where('role', 'participant')
            ->whereNotNull('city_id')
            ->with(['city', 'participantProfile'])
            ->chunkById(100, function ($users) use ($disk, $extractor, &$report) {
                foreach ($users as $user) {
                    $report['checked']++;

                    $profile = $user->participantProfile ?? new ParticipantProfile(['user_id' => $user->id]);

                    if ($profile->contract_imported_at !== null) {
                        $report['skipped_existing_import']++;
                        continue;
                    }

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
                        continue;
                    }

                    $this->fillIfMissing($user, $profile, $parsed);

                    if (!$profile->exists && empty($profile->birthday)) {
                        $report['skipped_unparseable']++;
                        continue;
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
            'summary' => $report,
        ];

        Storage::disk('local')->put($reportPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        Log::info('Participant contract import finished', [
            'report_path' => $reportPath,
            ...$payload,
        ]);

        $this->info('Contract import done: ' . json_encode($report));
        $this->info('Trigger: ' . $trigger . ', timezone: ' . config('app.timezone') . ', dry-run: ' . ($isDryRun ? 'yes' : 'no'));
        $this->info('Report written to storage/app/private/' . $reportPath);
        $this->info('Log written to storage/logs/laravel.log (and storage/logs/contracts-import.log when scheduled).');

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
}
