<?php

namespace Tests\Feature\Tags;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreTagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_a_tag()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson(route('tag.store'), [
            'name' => 'some-name',
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('tags', 1);
    }
}
