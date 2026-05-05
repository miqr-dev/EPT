<?php

namespace App\Http\Controllers;

use App\Models\CollaborationNews;
use App\Models\CollaborationSuggestion;
use App\Models\CollaborationSuggestionVote;
use App\Models\CollaborationTodo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CollaborationCenterController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);

        return Inertia::render('CollaborationCenter', [
            'newsItems' => CollaborationNews::with('author:id,name')->latest()->get(),
            'todos' => CollaborationTodo::with('author:id,name')->latest()->get(),
            'suggestions' => CollaborationSuggestion::with(['author:id,name', 'votes.user:id,name'])
                ->where('is_hidden', false)
                ->latest()
                ->get(),
        ]);
    }

    public function storeNews(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'required|string']);
        CollaborationNews::create($data + ['created_by' => $request->user()->id]);
        return back();
    }

    public function updateNews(Request $request, CollaborationNews $news): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate(['title' => 'required|string|max:255', 'content' => 'required|string']);
        $news->update($data);
        return back();
    }

    public function deleteNews(Request $request, CollaborationNews $news): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $news->delete();
        return back();
    }

    public function storeTodo(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate(['task' => 'required|string']);
        CollaborationTodo::create($data + ['created_by' => $request->user()->id]);
        return back();
    }

    public function updateTodo(Request $request, CollaborationTodo $todo): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $data = $request->validate(['task' => 'sometimes|string', 'is_completed' => 'sometimes|boolean']);
        $todo->update($data);
        return back();
    }

    public function deleteTodo(Request $request, CollaborationTodo $todo): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $todo->delete();
        return back();
    }

    public function storeSuggestion(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);
        $data = $request->validate(['content' => 'required|string']);
        CollaborationSuggestion::create(['title' => 'Vorschlag', 'content' => $data['content'], 'created_by' => $request->user()->id]);
        return back();
    }

    public function voteSuggestion(Request $request, CollaborationSuggestion $suggestion): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);
        $data = $request->validate([
            'vote' => 'nullable|in:like,dislike',
            'comment' => 'nullable|string',
            'delete_comment' => 'nullable|boolean',
        ]);

        if (empty($data['vote'])) {
            CollaborationSuggestionVote::where('suggestion_id', $suggestion->id)->where('user_id', $request->user()->id)->delete();
            return back();
        }

        $existingVote = CollaborationSuggestionVote::where('suggestion_id', $suggestion->id)
            ->where('user_id', $request->user()->id)
            ->first();

        $comment = $existingVote?->comment;

        if ($data['vote'] === 'like') {
            $comment = null;
        } elseif ($data['vote'] === 'dislike') {
            if (array_key_exists('comment', $data)) {
                $comment = $data['comment'];
            }
        }

        if (($data['delete_comment'] ?? false) === true) {
            $comment = null;
        }

        CollaborationSuggestionVote::updateOrCreate(
            ['suggestion_id' => $suggestion->id, 'user_id' => $request->user()->id],
            ['vote' => $data['vote'], 'comment' => $comment]
        );

        return back();
    }

    public function promoteSuggestion(Request $request, CollaborationSuggestion $suggestion): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        CollaborationTodo::firstOrCreate(
            ['suggestion_id' => $suggestion->id],
            ['task' => $suggestion->title . ': ' . $suggestion->content, 'created_by' => $request->user()->id]
        );

        $suggestion->update(['status' => 'promoted']);
        return back();
    }


    public function deleteSuggestion(Request $request, CollaborationSuggestion $suggestion): RedirectResponse
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'teacher'], true), 403);
        abort_unless($suggestion->created_by === $request->user()->id, 403);

        $suggestion->delete();
        return back();
    }

    public function hideSuggestion(Request $request, CollaborationSuggestion $suggestion): RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);
        $suggestion->update(['is_hidden' => true]);
        return back();
    }
}
