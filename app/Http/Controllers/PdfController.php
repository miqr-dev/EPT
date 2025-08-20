<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class PdfController extends Controller
{
    public function downloadMrtAPdf(Request $request)
    {
        $results = json_decode($request->query('results'), true);

        $html = view('pdfs.mrt-a-result-pdf', ['results' => $results])->render();

        $pdf = Browsershot::html($html)
            ->setNodeBinary(config('app.node_binary'))
            ->setNpmBinary(config('app.npm_binary'))
            ->windowSize(1024, 768)
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="mrt-a-results.pdf"');
    }
}
