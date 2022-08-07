<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowRecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_show_their_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);
        $recipe = Recipe::factory()
            ->hasAttached(
                $ingredient,
                ['amount' => 1]
            )->create([
                'user_id' => $user->id,
            ]);

        $response = $this->getJson(route('recipe.show', $recipe));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $recipe->name,
            'ingredients' => [
                [
                    'name' => $recipe->ingredients[0]->name,
                    'amount' => $recipe->ingredients[0]->pivot->amount,
                ],
            ],
        ]);
    }

    /** @test */
    public function a_user_cannot_show_another_users_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);
        $recipe = Recipe::factory()
            ->hasAttached(
                $ingredient,
                ['amount' => 1]
            )->create([
                'user_id' => $someOtherUser->id,
            ]);

        $response = $this->getJson(route('recipe.show', $recipe));

        $response->assertStatus(404);
        $response->assertJsonMissing([
            'name' => $recipe->name,
            'ingredients' => [
                [
                    'name' => $recipe->ingredients[0]->name,
                    'amount' => $recipe->ingredients[0]->pivot->amount,
                ],
            ],
        ]);
    }
}
