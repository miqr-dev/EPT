<?php

namespace Tests\Unit;

use App\Models\Normtable;
use App\Models\Test as TestModel; // Aliasing Test to avoid conflict with PHPUnit\Framework\Test
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NormtableTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_retrieve_normtable(): void
    {
        $normtable = Normtable::factory()->create();
        $this->assertDatabaseHas('normtables', ['id' => $normtable->id]);

        $retrievedNormtable = Normtable::find($normtable->id);
        $this->assertNotNull($retrievedNormtable);
        $this->assertInstanceOf(Normtable::class, $retrievedNormtable);
        $this->assertEquals($normtable->name, $retrievedNormtable->name);
    }

    public function test_normtable_relationships(): void
    {
        $normtable = Normtable::factory()->create();

        // Test Tests relationship (hasMany)
        // In TestFactory, 'scoring_table_id' is already associated with Normtable::factory()
        // So, creating Tests directly that use this normtable might be tricky without modification
        // or by overriding the factory state.
        // For this test, let's create tests and explicitly set the scoring_table_id.

        TestModel::factory()->count(2)->create(['scoring_table_id' => $normtable->id]);

        $normtable->load('tests'); // Eager load

        $this->assertCount(2, $normtable->tests);
        $this->assertInstanceOf(TestModel::class, $normtable->tests->first());
    }
}
