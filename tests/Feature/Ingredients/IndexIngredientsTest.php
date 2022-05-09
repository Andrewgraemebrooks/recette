<?php

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexIngredientsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_get_a_list_of_all_their_ingredients()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredients = Ingredient::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        $response = $this->getJson(route('ingredient.index'));

        $response->assertOk();
        foreach ($ingredients as $ingredient) {
            $response->assertJsonFragment([
                'name' => $ingredient->name
            ]);
            $response->assertJsonMissing([
                'amount' => null
            ]);
        }
    }

    /** @test */
    public function a_user_cannot_get_another_users_ingredients()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $ingredients = Ingredient::factory()->count(3)->create([
            'user_id' => $someOtherUser->id
        ]);

        $response = $this->getJson(route('ingredient.index'));

        $response->assertOk();
        foreach ($ingredients as $ingredient) {
            $response->assertJsonMissing([
                'name' => $ingredient->name
            ]);
        }
    }

}
