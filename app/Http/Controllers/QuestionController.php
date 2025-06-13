<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Test;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
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

        return Inertia::render('settings/QuestionsPage', [
            'questions' => $questions,
            'filters' => $request->only(['search']),
            'tests' => $tests,
        ]);
    }

    // store method from previous step
    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:255',
            'type' => 'required|string|in:multiple_choice,true_false,short_answer',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'answer' => 'required|string',
            'test_id' => 'nullable|exists:tests,id',
        ]);

        Question::create($validated);

        return redirect()->route('einstellungen.fragen')->with('success', 'Question created successfully.');
    }

    // update method from previous step
    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:255',
            'type' => 'required|string|in:multiple_choice,true_false,short_answer',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string',
            'answer' => 'required|string',
            'test_id' => 'nullable|exists:tests,id',
        ]);

        $question->update($validated);

        return redirect()->route('einstellungen.fragen')->with('success', 'Question updated successfully.');
    }

    // destroy method from previous step
    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('einstellungen.fragen')->with('success', 'Question deleted successfully.');
    }

    // Other methods like create, edit, show if they were fully implemented.
    // For this scope, index, store, update, destroy are key for the CRUD page.
}
