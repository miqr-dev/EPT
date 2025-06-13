<?php

namespace Tests\Feature\CustomSettings;

use App\Models\User; // Or your specific user model for authentication
use App\Models\Question;
use App\Models\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class EinstellungenTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Test::factory()->create(['name' => 'Sample Test']); // For question assignment
    }

    public function test_einstellungen_page_is_displayed(): void
    {
        $this->get(route('einstellungen.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('custom_settings/EinstellungenPage')
                // Optionally, assert that a link to 'einstellungen.fragen' exists
            );
    }

    public function test_einstellungen_page_links_to_fragen_page(): void
    {
        // This test is implicitly covered by accessing einstellungen.fragen route,
        // but if you want to check the link specifically, it's harder with Inertia server-side tests.
        // A good enough check is that the 'einstellungen.fragen' route works.
        $this->get(route('einstellungen.fragen'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('custom_settings/FragenPage')
                ->has('questions')
                ->has('tests')
            );
    }

    public function test_fragen_crud_page_is_displayed_with_data(): void
    {
        Question::factory()->count(3)->create();

        $this->get(route('einstellungen.fragen'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('custom_settings/FragenPage')
                ->has('questions.data', 3)
                ->has('tests')
            );
    }

    public function test_can_create_a_new_question_via_einstellungen_flow(): void
    {
        $test = Test::first();
        $questionData = [
            'text' => 'Was ist die Hauptstadt von Deutschland?',
            'type' => 'short_answer',
            'answer' => 'Berlin',
            'test_id' => $test->id,
        ];

        $this->post(route('einstellungen.questions.store'), $questionData)
            ->assertRedirect(route('einstellungen.fragen'))
            ->assertSessionHas('success', 'Question created successfully.');

        $this->assertDatabaseHas('questions', [
            'text' => 'Was ist die Hauptstadt von Deutschland?',
            'answer' => 'Berlin',
            'test_id' => $test->id,
        ]);
    }

    public function test_question_creation_fails_with_invalid_data_via_einstellungen_flow(): void
    {
        $this->post(route('einstellungen.questions.store'), [
            'text' => '', // Invalid
            'type' => 'unknown_type', // Invalid
            'answer' => '', // Invalid
        ])
        ->assertSessionHasErrors(['text', 'type', 'answer']);
    }

    public function test_can_update_a_question_via_einstellungen_flow(): void
    {
        $question = Question::factory()->create();
        $test = Test::first();
        $updatedData = [
            'text' => 'Aktualisiert: Was ist 2 mal 3?',
            'type' => $question->type,
            'answer' => '6',
            'test_id' => $test->id,
            'options' => $question->type === 'multiple_choice' ? $question->options : null,
        ];

        $this->put(route('einstellungen.questions.update', $question->id), $updatedData)
            ->assertRedirect(route('einstellungen.fragen'))
            ->assertSessionHas('success', 'Question updated successfully.');

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'text' => 'Aktualisiert: Was ist 2 mal 3?',
            'answer' => '6',
        ]);
    }

    public function test_can_delete_a_question_via_einstellungen_flow(): void
    {
        $question = Question::factory()->create();

        $this->delete(route('einstellungen.questions.destroy', $question->id))
            ->assertRedirect(route('einstellungen.fragen'))
            ->assertSessionHas('success', 'Question deleted successfully.');

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }

    public function test_fragen_page_search_filter_works_via_einstellungen_flow(): void
    {
        Question::factory()->create(['text' => 'Einzigartige Frage Alpha']);
        Question::factory()->create(['text' => 'Normale Frage Beta']);
        Question::factory()->create(['text' => 'Weitere Normale Frage Gamma']);

        $this->get(route('einstellungen.fragen', ['search' => 'Einzigartige']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('custom_settings/FragenPage')
                ->has('questions.data', 1)
                ->where('questions.data.0.text', 'Einzigartige Frage Alpha')
            );

        $this->get(route('einstellungen.fragen', ['search' => 'Normale']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('questions.data', 2)
            );
    }
}
