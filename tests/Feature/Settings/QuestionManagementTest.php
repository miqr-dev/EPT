<?php

namespace Tests\Feature\Settings;

use App\Models\User; // Or Admin, depending on your authentication for settings
use App\Models\Question;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class QuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user.
        // Adjust this based on your application's authentication (User, Admin, etc.)
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a dummy test for assignment
        Test::factory()->create(['name' => 'Sample Test']);
    }

    public function test_questions_page_is_displayed(): void
    {
        $response = $this->get(route('questions.index'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('settings/QuestionsPage')
            ->has('questions.data')
            ->has('tests') // Check if tests are passed for the dropdown
        );
    }

    public function test_can_create_a_new_question(): void
    {
        $test = Test::first();
        $questionData = [
            'text' => 'What is 2 + 2?',
            'type' => 'short_answer',
            'answer' => '4',
            'test_id' => $test->id,
            'options' => null, // Explicitly null for non-mcq
        ];

        $response = $this->post(route('questions.store'), $questionData);

        $response->assertRedirect(route('questions.index'));
        $response->assertSessionHas('success', 'Question created successfully.');
        $this->assertDatabaseHas('questions', [
            'text' => 'What is 2 + 2?',
            'type' => 'short_answer',
            'answer' => '4',
            'test_id' => $test->id,
        ]);
    }

    public function test_can_create_a_multiple_choice_question(): void
    {
        $test = Test::first();
        $questionData = [
            'text' => 'Which is a PHP framework?',
            'type' => 'multiple_choice',
            'options' => ['Laravel', 'React', 'Vue'],
            'answer' => 'Laravel',
            'test_id' => $test->id,
        ];

        $response = $this->post(route('questions.store'), $questionData);
        $response->assertRedirect(route('questions.index'));
        $this->assertDatabaseHas('questions', [
            'text' => 'Which is a PHP framework?',
            'options' => json_encode(['Laravel', 'React', 'Vue']), // Check options are stored correctly (likely as JSON)
            'answer' => 'Laravel',
        ]);
    }

    public function test_question_creation_fails_with_invalid_data(): void
    {
        $response = $this->post(route('questions.store'), [
            'text' => '', // Invalid: text is required
            'type' => 'invalid_type', // Invalid: type is not one of the allowed
            'answer' => '', // Invalid: answer is required
        ]);

        $response->assertSessionHasErrors(['text', 'type', 'answer']);
    }

    public function test_can_update_a_question(): void
    {
        $question = Question::factory()->create();
        $test = Test::first();
        $updatedData = [
            'text' => 'Updated: What is the capital of France?',
            'type' => 'short_answer',
            'answer' => 'Paris',
            'test_id' => $test->id,
            'options' => null,
        ];

        $response = $this->put(route('questions.update', $question->id), $updatedData);

        $response->assertRedirect(route('questions.index'));
        $response->assertSessionHas('success', 'Question updated successfully.');
        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'text' => 'Updated: What is the capital of France?',
            'answer' => 'Paris',
            'test_id' => $test->id,
        ]);
    }

    public function test_can_delete_a_question(): void
    {
        $question = Question::factory()->create();

        $response = $this->delete(route('questions.destroy', $question->id));

        $response->assertRedirect(route('questions.index'));
        $response->assertSessionHas('success', 'Question deleted successfully.');
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    public function test_settings_page_is_displayed_and_links_to_questions(): void
    {
        $response = $this->get(route('settings.index'));
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('settings/SettingsPage')
            // You might want to check for a link to settings.questions here
            // This is harder to test directly with Inertia assertions for specific link hrefs
            // but component rendering is a good start.
        );
    }

    // It might be beneficial to also test the search/filter functionality
    public function test_questions_page_search_filter_works()
    {
        Question::factory()->create(['text' => 'Unique Question Alpha']);
        Question::factory()->create(['text' => 'Common Question Beta']);
        Question::factory()->create(['text' => 'Another Common Question Gamma']);

        $response = $this->get(route('questions.index', ['search' => 'Unique']));
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('settings/QuestionsPage')
            ->has('questions.data', 1) // Expecting one result
            ->where('questions.data.0.text', 'Unique Question Alpha')
        );

        $response = $this->get(route('questions.index', ['search' => 'Common']));
        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->has('questions.data', 2) // Expecting two results
        );
    }
}
