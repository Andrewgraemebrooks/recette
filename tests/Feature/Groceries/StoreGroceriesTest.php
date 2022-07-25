<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreGroceriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getGroceryData();

        $response = $this->postJson(route('grocery.store'), $data);

        $response->assertCreated();
        $response->assertJsonFragment($this->getGroceryData());
        $this->assertDatabaseHas('grocery', [
            'name' => 'some-grocery'
        ]);
    }

    protected function getGroceryData($merge = []): array
    {
        return array_merge([
            'name' => 'some-grocery',
            'amount' => rand(0, 15)
        ], $merge);
    }

}
