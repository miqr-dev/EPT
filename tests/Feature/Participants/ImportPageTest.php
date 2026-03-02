<?php

namespace Tests\Feature\Participants;

use App\Models\City;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ImportPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_page_sorts_imported_users_by_latest_update_first_within_city(): void
    {
        $city = City::create(['name' => 'Berlin']);
        $otherCity = City::create(['name' => 'Hamburg']);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'firstname' => 'One',
            'username' => 'admin.one',
            'role' => 'admin',
            'city_id' => $city->id,
        ]);

        $oldestInCity = User::factory()->create([
            'name' => 'Alpha',
            'firstname' => 'A',
            'username' => 'alpha.user',
            'role' => 'participant',
            'city_id' => $city->id,
            'updated_at' => now()->subDays(30),
        ]);

        $newestInCity = User::factory()->create([
            'name' => 'Zulu',
            'firstname' => 'Z',
            'username' => 'zulu.user',
            'role' => 'teacher',
            'city_id' => $city->id,
            'updated_at' => now()->subMinute(),
        ]);

        User::factory()->create([
            'name' => 'Other',
            'firstname' => 'City',
            'username' => 'other.city',
            'role' => 'participant',
            'city_id' => $otherCity->id,
            'updated_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('participants.import.page'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Participants/Import')
            ->has('users.data', 2)
            ->where('users.data.0.id', $newestInCity->id)
            ->where('users.data.1.id', $oldestInCity->id)
        );
    }
}
