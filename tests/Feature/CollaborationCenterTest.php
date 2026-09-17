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
    $suggestedAt = now()->subDays(3)->setTime(9, 30);

    $promotedSuggestion = CollaborationSuggestion::create([
        'title' => 'Vorschlag',
        'content' => 'Promote this idea',
        'created_by' => $teacher->id,
    ]);
    $promotedSuggestion->forceFill([
        'created_at' => $suggestedAt,
        'updated_at' => $suggestedAt,
    ])->save();

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

    $todo = CollaborationTodo::where('suggestion_id', $promotedSuggestion->id)->firstOrFail();

    expect($todo->created_by)->toBe($teacher->id)
        ->and($todo->created_at->toDateTimeString())->toBe($suggestedAt->toDateTimeString());

    $this->actingAs($admin)
        ->get(route('collaboration.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CollaborationCenter')
            ->has('suggestions', 1)
            ->where('suggestions.0.id', $openSuggestion->id));
});

test('teachers cannot promote suggestions into todos', function () {
    $teacher = createCollaborationUser('teacher');

    $suggestion = CollaborationSuggestion::create([
        'title' => 'Vorschlag',
        'content' => 'Teacher should not promote this idea',
        'created_by' => $teacher->id,
    ]);

    $this->actingAs($teacher)
        ->post(route('collaboration.suggestions.promote', $suggestion))
        ->assertForbidden();

    $suggestion->refresh();

    expect(CollaborationTodo::where('suggestion_id', $suggestion->id)->exists())->toBeFalse()
        ->and($suggestion->status)->toBe('open')
        ->and($suggestion->is_hidden)->toBeFalse();
});

test('completed todos are sent to the collaboration page as completed', function () {
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

test('collaboration sidebar notifications count recent visible news suggestions and todos', function () {
    $admin = createCollaborationUser('admin');
    $teacher = createCollaborationUser('teacher');

    $recentNews = CollaborationNews::create([
        'title' => 'Update',
        'content' => 'New collaboration update',
        'created_by' => $admin->id,
    ]);

    $recentSuggestion = CollaborationSuggestion::create([
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

    $recentTodo = CollaborationTodo::create([
        'task' => 'Recent todo',
        'created_by' => $admin->id,
    ]);

    foreach ([$recentNews, $recentSuggestion, $recentTodo] as $model) {
        $model->forceFill([
            'created_at' => now()->subDays(13),
            'updated_at' => now()->subDays(13),
        ])->save();
    }

    $oldNews = CollaborationNews::create([
        'title' => 'Old update',
        'content' => 'Older collaboration update',
        'created_by' => $admin->id,
    ]);

    $oldSuggestion = CollaborationSuggestion::create([
        'title' => 'Vorschlag',
        'content' => 'Old visible idea',
        'created_by' => $teacher->id,
    ]);

    $oldTodo = CollaborationTodo::create([
        'task' => 'Old todo',
        'created_by' => $admin->id,
    ]);

    foreach ([$oldNews, $oldSuggestion, $oldTodo] as $model) {
        $model->forceFill([
            'created_at' => now()->subDays(15),
            'updated_at' => now()->subDays(15),
        ])->save();
    }

    $this->actingAs($admin)
        ->get(route('collaboration.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('collaborationNotifications.news', 1)
            ->where('collaborationNotifications.suggestions', 1)
            ->where('collaborationNotifications.todos', 1)
            ->where('collaborationNotifications.total', 3));
});

test('admins can publish sanitized rich news updates', function () {
    $admin = createCollaborationUser('admin');

    $this->actingAs($admin)
        ->post(route('collaboration.news.store'), [
            'title' => 'Rich update',
            'content' => '<p><strong>Important</strong> <span style="color: #ef4444; font-size: 20px; position: absolute; background-image: url(javascript:alert(1))">notice</span><script>alert("x")</script><img src=x onerror=alert(1)></p>',
        ])
        ->assertRedirect();

    $news = CollaborationNews::firstOrFail();

    expect($news->content)
        ->toContain('<strong>Important</strong>')
        ->toContain('style="color: #ef4444; font-size: 20px"')
        ->not->toContain('<script')
        ->not->toContain('<img')
        ->not->toContain('onerror')
        ->not->toContain('position')
        ->not->toContain('url(');
});
