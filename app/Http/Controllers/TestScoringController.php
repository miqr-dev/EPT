<?php

namespace App\Http\Controllers;

use App\Models\ExamStep;
use App\Models\Normtable;
use App\Models\TestAssignment;
use App\Models\TestResult;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TestScoringController extends Controller
{
    public function scoreBrtA(Request $request)
    {
        $validatedData = $request->validate([
            'answers' => 'required|array',
            'exam_step_id' => 'required|integer|exists:exam_steps,id',
        ]);

        $user = Auth::user();
        $examStep = ExamStep::with('test')->findOrFail($validatedData['exam_step_id']);
        $testId = $examStep->test->id;
        $userAnswers = $validatedData['answers'];

        // Hardcoded questions and answers from BRT-A.vue
        $questions = [
            ["text" => "619020 – 541600 = ?", "answers" => ["77420"]],
            ["text" => "619020 = 174309 + ?", "answers" => ["444711"]],
            ["text" => "4 : 80 = ?", "answers" => ["0,05"]],
            ["text" => "0,2 · ____ = 0,1", "answers" => ["0,5", "1/2"]],
            ["text" => "1/3 : 1/2 = ?", "answers" => ["2/3"]],
            ["text" => "Verwandle 0,4 in einen gewöhnlichen Bruch.", "answers" => ["2/5", "4/10"]],
            ["text" => "Ein Mechaniker hat aus 3 Teilen ein Gerät hergestellt. Die Einzelteile wiegen: 50 g, 9,4 kg, 1050 g. Wie viel wiegt das gesamte Gerät?", "answers" => ["10500", "10500 g", "10500g", "10,5", "10,5Kg", "10,5kg", "10,5 Kg", "10,5 kg"]],
            ["text" => "Rechne um: Wie viel g sind 9 kg und 1 g?", "answers" => ["9001", "9001g", "9001 g", "9001 G", "9001G"]],
            ["text" => "Wie viel Zinsen erbringen 1000 € zu 4 % in einem Jahr?", "answers" => ["40", "40€", "40 €"]],
            ["text" => "Rudi kauft sich ein neues Mofa. Es kostet 1390 €. Bei Barzahlung bekommt er 2 % Rabatt. Wie viel muss er bezahlen?", "answers" => ["1362,2", "1362,20", "1362,20 €", "1362,2 €", "1362,2€", "1362,20€"]],
            ["text" => "Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie viele Bretter erhält man, wenn sie 2 cm dick sind?", "answers" => ["30"]],
            ["text" => "Im Sägewerk können aus einem Baumstamm 20 Bretter von 3 cm Dicke geschnitten werden. Wie dick wird ein Brett, wenn man 15 Bretter aus dem Stamm schneidet?", "answers" => ["4", "4cm", "4 cm", "4 Cm", "4Cm"]],
            ["text" => "Berechne die Grundstücksgröße in m².", "answers" => ["930", "930 m²", "930m²", "930 m2"]],
            ["text" => "Das Rad hat einen Durchmesser von 0,6 m. Welche Strecke legt es zurück, wenn es sich 100 mal dreht?", "answers" => ["188,4", "188,4 m", "188,4m"]],
            ["text" => "√81 = ?", "answers" => ["9"]],
            ["text" => "10³ = ?", "answers" => ["1000"]],
        ];

        // Scoring logic adapted from BRT-A.vue
        $finalScore = 0;
        $detailedAnswers = [];
        foreach ($questions as $i => $question) {
            $userAnswer = $userAnswers[$i]['user_answer'] ?? '';
            $isCorrect = $this->isCorrectAnswer($userAnswer, $question['answers']);
            if ($isCorrect) {
                $finalScore++;
            }
            $detailedAnswers[] = [
                'question' => $question['text'],
                'user_answer' => $userAnswer,
                'correct_answers' => $question['answers'],
                'time_seconds' => $userAnswers[$i]['time_seconds'] ?? 0,
                'is_correct' => $isCorrect,
            ];
        }

        // Norming
        $rohwertToPrTable = Normtable::where('name', 'BRT-A Rohwert to PR')->firstOrFail()->table_data;
        $prToTwertTable = Normtable::where('name', 'BRT-A PR to T-Wert')->firstOrFail()->table_data;

        $prozentrang = $this->getPRFromRohwert($finalScore, $rohwertToPrTable);
        $twert = $this->getTwertFromPR($prozentrang, $prToTwertTable);

        // Prepare results for DB
        $results = [
            'rohwert' => $finalScore,
            'prozentrang' => $prozentrang,
            'twert' => $twert,
            'total_time_seconds' => array_sum(array_column($detailedAnswers, 'time_seconds')),
            'answers' => $detailedAnswers,
        ];

        // Save Test Result
        $assignment = TestAssignment::firstOrCreate(
            ['participant_id' => $user->id, 'test_id' => $testId],
            ['status' => 'assigned']
        );

        $testResult = TestResult::create([
            'assignment_id' => $assignment->id,
            'result_json' => $results,
        ]);

        // Generate and save PDF
        $pdf = Pdf::loadView('pdf.brt-a-result', [
            'testName' => $examStep->test->name,
            'date' => now()->format('d.m.Y'),
            'results' => $results,
        ]);
        $pdfPath = "test_results/brt-a-{$testResult->id}.pdf";
        Storage::disk('private')->put($pdfPath, $pdf->output());

        $testResult->update(['pdf_path' => $pdfPath]);

        $assignment->update(['status' => 'completed', 'completed_at' => now()]);

        return response()->json(['message' => 'Test scored and saved successfully.', 'test_result_id' => $testResult->id]);
    }

    private function isCorrectAnswer(string $userAnswer, array $validAnswers): bool
    {
        if (empty($userAnswer)) return false;

        $normalize = fn($answer) => strtolower(str_replace(',', '.', preg_replace('/[€%$]/', '', preg_replace('/\s+/', '', trim($answer)))));

        $normalizedUser = $normalize($userAnswer);

        foreach ($validAnswers as $correct) {
            if ($normalizedUser === $normalize($correct)) {
                return true;
            }
        }
        return false;
    }

    private function getPRFromRohwert(int $rohwert, array $table): int
    {
        return $table[$rohwert] ?? 0;
    }

    private function getTwertFromPR(int $pr, array $table): int
    {
        $best = $table[0];
        foreach ($table as $entry) {
            if ($pr >= $entry['pr']) {
                $best = $entry;
            }
        }
        return $best['t'];
    }
}
