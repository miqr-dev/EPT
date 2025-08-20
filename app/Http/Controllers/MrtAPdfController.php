<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class MrtAPdfController extends Controller
{
    public function __invoke(Request $request, string $type)
    {
        $body = $request->input('html', '');
        $html = '<html><head><meta charset="utf-8"></head><body>' . $body . '</body></html>';

        $pdf = Browsershot::html($html)
            ->showBackground()
            ->format('A4')
            ->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $type . '.pdf"',
        ]);
    }
}

