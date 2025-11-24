<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;
use App\Events\PdfPageChanged;
use App\Events\PdfViewClosed;
use App\Models\User;

class PdfController extends Controller
{
    public function showPdf(Request $request)
    {
        $examId = $request->input('exam_id');
        // Broadcast event to show PDF page 1 to all participants in the exam
        broadcast(new PdfPageChanged($examId, 1))->toOthers();

        return response()->json(['status' => 'success']);
    }

    public function changePage(Request $request)
    {
        $examId = $request->input('exam_id');
        $page = $request->input('page');

        // Broadcast event to change the PDF page for all participants in the exam
        broadcast(new PdfPageChanged($examId, $page))->toOthers();

        return response()->json(['status' => 'success']);
    }

    public function closePdf(Request $request)
    {
        $examId = $request->input('exam_id');

        // Broadcast event to close the PDF view for all participants in the exam
        broadcast(new PdfViewClosed($examId))->toOthers();

        return response()->json(['status' => 'success']);
    }

    public function getPdfPage(Request $request)
    {
        $user = $request->user();
        $page = $request->input('page', 1);

        $pdfPath = $this->getParticipantPdfPath($user);

        if (!Storage::disk('windows_it_test')->exists($pdfPath)) {
            return response()->json(['error' => 'Ihre PDF-Datei konnte nicht gefunden werden.'], 404);
        }

        $pathToPdf = Storage::disk('windows_it_test')->path($pdfPath);
        $imagePath = storage_path('app/public/pdf_images/' . $user->id . '_page_' . $page . '.jpg');

        try {
            $pdf = new Pdf($pathToPdf);
            $pdf->setPage($page)->saveImage($imagePath);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Fehler beim Extrahieren der PDF-Seite.'], 500);
        }

        return response()->json(['image_url' => Storage::url('pdf_images/' . $user->id . '_page_' . $page . '.jpg')]);
    }

    private function getParticipantPdfPath(User $user)
    {
        $nameParts = explode(' ', $user->name);
        $lastName = array_pop($nameParts);
        $firstName = implode(' ', $nameParts);
        return $lastName . '-' . $firstName . '.pdf';
    }
}
