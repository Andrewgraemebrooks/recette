<?php

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteIngredientsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_delete_their_ingredient()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->deleteJson(route('ingredient.destroy', $ingredient));

        $response->assertNoContent();
        $this->assertDatabaseMissing('ingredients', [
            'id' => $ingredient->id
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_ingredient()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id
        ]);

        $response = $this->deleteJson(route('ingredient.destroy', $ingredient));

        $response->assertStatus(404);
        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id
        ]);
    }
}
