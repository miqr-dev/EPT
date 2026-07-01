<?php

use App\Models\CollaborationNews;
use App\Models\CollaborationSuggestion;
use App\Models\CollaborationTodo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createCollaborationUser(string $role): User
{
    return User::create([
        'name' => fake()->name(),
        'firstname' => fake()->firstName(),
        'username' => fake()->unique()->userName(),
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => $role,
    ]);
}

test('promoted suggestions are turned into todos and removed from the suggestions feed', function () {
    $admin = createCollaborationUser('admin');
    $teacher = createCollaborationUser('teacher');

    $promotedSuggestion = CollaborationSuggestion::create([
        'title' => 'Vorschlag',
        'content' => 'Promote this idea',
        'created_by' => $teacher->id,
    ]);

    $openSuggestion = CollaborationSuggestion::create([
        'title' => 'Vorschlag',
        'content' => 'Keep this idea open',
        'created_by' => $teacher->id,
    ]);

    $this->actingAs($admin)
        ->post(route('collaboration.suggestions.promote', $promotedSuggestion))
        ->assertRedirect();

    $promotedSuggestion->refresh();

    expect($promotedSuggestion->status)->toBe('promoted')
        ->and($promotedSuggestion->is_hidden)->toBeTrue()
        ->and(CollaborationTodo::where('suggestion_id', $promotedSuggestion->id)->exists())->toBeTrue();

    $this->actingAs($admin)
        ->get(route('collaboration.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CollaborationCenter')
            ->has('suggestions', 1)
            ->where('suggestions.0.id', $openSuggestion->id));
});

test('completed todos are sent to the collaboration summary as completed', function () {
    $admin = createCollaborationUser('admin');

    $todo = CollaborationTodo::create([
        'task' => 'Finished task',
        'is_completed' => true,
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('collaboration.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CollaborationCenter')
            ->where('todos.0.id', $todo->id)
            ->where('todos.0.is_completed', true));
});

test('collaboration sidebar notifications count visible suggestions and news', function () {
    $admin = createCollaborationUser('admin');
    $teacher = createCollaborationUser('teacher');

    CollaborationNews::create([
        'title' => 'Update',
        'content' => 'New collaboration update',
        'created_by' => $admin->id,
    ]);

    CollaborationSuggestion::create([
        'title' => 'Vorschlag',
        'content' => 'Visible idea',
        'created_by' => $teacher->id,
    ]);

    CollaborationSuggestion::create([
        'title' => 'Vorschlag',
        'content' => 'Hidden idea',
        'created_by' => $teacher->id,
        'status' => 'promoted',
        'is_hidden' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('collaboration.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('collaborationNotifications.news', 1)
            ->where('collaborationNotifications.suggestions', 1)
            ->where('collaborationNotifications.total', 2));
});
