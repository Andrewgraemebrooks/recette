<?php

use App\Models\Grocery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowGroceriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_show_a_specific_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $grocery = Grocery::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('grocery.show', $grocery));

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $grocery->id,
        ]);
    }

    /** @test */
    public function a_user_cannot_show_another_users_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $otherUser = User::factory()->create();
        $grocery = Grocery::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->get(route('grocery.show', $grocery));

        $response->assertNotFound();
    }
}
