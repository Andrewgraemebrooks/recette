<?php

use App\Models\Grocery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexGroceriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_list_of_all_the_users_groceries()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $groceries = Grocery::factory(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson(route('grocery.index'));

        $response->assertOk();
        $groceries->each(fn ($grocery) => (
            $response->assertJsonFragment([
                'id' => $grocery->id,
            ])
        ));
    }

    /** @test */
    public function the_user_does_not_get_another_users_groceries()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $otherUser = User::factory()->create();
        $groceries = Grocery::factory(3)->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->getJson(route('grocery.index'));

        $response->assertOk();
        $groceries->each(fn ($grocery) => (
            $response->assertJsonMissing([
                'id' => $grocery->id,
            ])
        ));
    }
}
