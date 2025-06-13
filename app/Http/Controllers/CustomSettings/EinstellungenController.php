<?php

namespace App\Http\Controllers\CustomSettings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Test; // Will be needed for FragenPage eventually
use App\Models\Question; // Will be needed for FragenPage eventually

class EinstellungenController extends Controller
{
    /**
     * Display the main Einstellungen page.
     */
    public function showEinstellungenPage()
    {
        return Inertia::render('custom_settings/EinstellungenPage');
    }

    /**
     * Display the Fragen (Questions) management page.
     * This will eventually be the Question CRUD page.
     */
    public function showFragenPage(Request $request)
    {
        // Initially, this might just render a placeholder page.
        // Later, it will fetch questions, tests, etc. similar to the previous QuestionController.

        // Placeholder data for now, will be built out in a later step
        $questions = Question::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('text', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%");
            })
            ->with('test')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $tests = Test::orderBy('name')->get(['id', 'name']);

        return Inertia::render('custom_settings/FragenPage', [
            'questions' => $questions,
            'filters' => $request->only(['search']),
            'tests' => $tests,
        ]);
    }
}
