<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class ChartService
{
    public static function generate(array $data): ?string
    {
        $dataJson = json_encode($data);
        $nodeScriptPath = resource_path('js/generate-chart.js');

        $result = Process::path(base_path())->node($nodeScriptPath, [$dataJson]);

        if ($result->successful()) {
            return $result->output();
        }

        return null;
    }
}
