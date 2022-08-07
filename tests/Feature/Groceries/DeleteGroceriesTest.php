<?php

use App\Models\Grocery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteGroceriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_delete_a_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $grocery = Grocery::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson(route('grocery.destroy', $grocery));

        $response->assertNoContent();
        $this->assertDatabaseMissing('groceries', [
            'id' => $grocery->id,
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $otherUser = User::factory()->create();
        $grocery = Grocery::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->deleteJson(route('grocery.destroy', $grocery));

        $response->assertNotFound();
        $this->assertDatabaseHas('groceries', [
            'id' => $grocery->id,
        ]);
    }
}
