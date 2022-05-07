<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteRecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_delete_their_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $recipe = Recipe::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->deleteJson(route('recipe.destroy', $recipe));

        $response->assertNoContent();
        $this->assertDatabaseMissing('recipes', [
            'id' => $recipe->id
        ]);
    }

    /** @test */
    public function a_user_cannot_delete_another_users_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'user_id' => $someOtherUser->id
        ]);

        $response = $this->deleteJson(route('recipe.destroy', $recipe));

        $response->assertStatus(404);
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id
        ]);
    }

}
