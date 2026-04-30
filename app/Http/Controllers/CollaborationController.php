<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Todo;
use App\Models\Suggestion;
use App\Models\SuggestionVote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CollaborationController extends Controller
{
    public function index()
    {
        return Inertia::render('Collaboration', [
            'news' => News::with('user:id,name,firstname')->latest()->get(),
            'todos' => Todo::latest()->get(),
            'suggestions' => Suggestion::with(['user:id,name,firstname', 'votes.user:id,name,firstname'])
                ->where('is_hidden', false)
                ->latest()
                ->get(),
            'auth' => [
                'user' => Auth::user(),
            ],
        ]);
    }

    // News
    public function storeNews(Request $request)
    {
        $this->authorizeAdmin();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        News::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);

        return back();
    }

    public function destroyNews(News $news)
    {
        $this->authorizeAdmin();
        $news->delete();
        return back();
    }

    // Todos
    public function storeTodo(Request $request)
    {
        $this->authorizeAdmin();
        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        Todo::create($validated);
        return back();
    }

    public function updateTodo(Request $request, Todo $todo)
    {
        $this->authorizeAdmin();
        $validated = $request->validate([
            'is_completed' => 'required|boolean',
        ]);

        $todo->update($validated);
        return back();
    }

    public function destroyTodo(Todo $todo)
    {
        $this->authorizeAdmin();
        $todo->delete();
        return back();
    }

    // Suggestions
    public function storeSuggestion(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        Suggestion::create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);

        return back();
    }

    public function hideSuggestion(Suggestion $suggestion)
    {
        $this->authorizeAdmin();
        $suggestion->update(['is_hidden' => true]);
        return back();
    }

    public function voteSuggestion(Request $request, Suggestion $suggestion)
    {
        $validated = $request->validate([
            'vote' => 'required|boolean',
            'comment' => 'nullable|string',
        ]);

        SuggestionVote::updateOrCreate(
            ['suggestion_id' => $suggestion->id, 'user_id' => Auth::id()],
            ['vote' => $validated['vote'], 'comment' => $validated['comment']]
        );

        return back();
    }

    public function removeVote(Suggestion $suggestion)
    {
        SuggestionVote::where('suggestion_id', $suggestion->id)
            ->where('user_id', Auth::id())
            ->delete();

        return back();
    }

    public function promoteSuggestion(Request $request, Suggestion $suggestion)
    {
        $this->authorizeAdmin();

        Todo::create([
            'description' => $suggestion->content,
        ]);

        return back();
    }

    private function authorizeAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }
    }
}
