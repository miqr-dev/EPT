<?php

namespace Tests\Unit;

use App\Models\Normtable;
use App\Models\Question;
use App\Models\Test as TestModel;
use App\Models\TestBattery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestModelTest extends TestCase // Renamed class
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_test_model(): void
    {
        $testModel = TestModel::factory()->create();
        $this->assertDatabaseHas('tests', ['id' => $testModel->id]);

        $retrievedTestModel = TestModel::find($testModel->id);
        $this->assertNotNull($retrievedTestModel);
        $this->assertInstanceOf(TestModel::class, $retrievedTestModel);
        $this->assertEquals($testModel->title, $retrievedTestModel->title);
    }

    public function test_test_model_relationships(): void
    {
        // questions() (hasMany)
        $testModelWithQuestions = TestModel::factory()->create();
        Question::factory()->for($testModelWithQuestions, 'test')->count(3)->create();
        $testModelWithQuestions->load('questions');
        $this->assertCount(3, $testModelWithQuestions->questions);
        $this->assertInstanceOf(Question::class, $testModelWithQuestions->questions->first());

        // normtable() (belongsTo)
        $normtable = Normtable::factory()->create();
        $testModelWithNormtable = TestModel::factory()->for($normtable, 'normtable')->create();
        $this->assertInstanceOf(Normtable::class, $testModelWithNormtable->normtable);

        // Nullable normtable
        $testModelNullNormtable = TestModel::factory()->create(['scoring_table_id' => null]);
        $this->assertNull($testModelNullNormtable->normtable);

        // testBatteries() (belongsToMany)
        $testModelForBattery = TestModel::factory()->create();
        $testBattery = TestBattery::factory()->create();
        $order = 1;
        $testModelForBattery->testBatteries()->attach($testBattery->id, ['order' => $order]);

        $this->assertCount(1, $testModelForBattery->testBatteries);
        $this->assertInstanceOf(TestBattery::class, $testModelForBattery->testBatteries->first());
        $this->assertEquals($order, $testModelForBattery->testBatteries->first()->pivot->order);
    }
}
