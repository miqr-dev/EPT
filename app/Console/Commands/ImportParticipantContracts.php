<?php

namespace App\Console\Commands;

use App\Models\ParticipantProfile;
use App\Models\User;
use App\Services\Contracts\PdfContractDataExtractor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportParticipantContracts extends Command
{
    protected $signature = 'participants:import-contracts {--report= : Optional storage-relative report path}';

    protected $description = 'Import participant contract data from PDF files and fill missing profile fields.';

    public function handle(PdfContractDataExtractor $extractor): int
    {
        $disk = Storage::disk('pdfs');
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

                    $profile->contract_imported_at = now();
                    $profile->save();

                    $report['imported']++;
                }
            });

        $reportPath = $this->option('report') ?: 'reports/contracts-import-' . now()->format('Ymd_His') . '.json';
        Storage::disk('local')->put($reportPath, json_encode([
            'ran_at' => now()->toIso8601String(),
            'summary' => $report,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('Contract import done: ' . json_encode($report));
        $this->info('Report written to storage/app/private/' . $reportPath);

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
