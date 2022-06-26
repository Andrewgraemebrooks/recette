<?php

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowIngredientTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_show_their_ingredient()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->getJson(route('ingredient.show', $ingredient));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $ingredient->name,
        ]);
    }

    /** @test */
    public function a_user_cannot_show_another_users_ingredient()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);

        $response = $this->getJson(route('ingredient.show', $ingredient));

        $response->assertStatus(404);
        $response->assertJsonMissing([
            'name' => $ingredient->name,
        ]);
    }
}
