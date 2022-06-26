<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexRecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_get_a_list_of_all_their_recipes()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);
        $recipes = Recipe::factory()
            ->count(2)
            ->hasAttached($ingredient, ['amount'=> 3])
            ->create([
                'user_id' => $user->id,
            ]);

        $response = $this->getJson(route('recipe.index'));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $recipes[0]->name,
            'ingredients' => [
                [
                    'name' => $recipes[0]->ingredients[0]->name,
                    'amount' => $recipes[0]->ingredients[0]->pivot->amount,
                ],
            ],
            'name' => $recipes[1]->name,
            'ingredients' => [
                [
                    'name' => $recipes[1]->ingredients[0]->name,
                    'amount' => $recipes[1]->ingredients[0]->pivot->amount,
                ],
            ],
        ]);
    }

    /** @test */
    public function a_user_cannot_get_another_users_recipes()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);
        $recipes = Recipe::factory()
            ->count(2)
            ->hasAttached($ingredient, ['amount'=> 3])
            ->create([
                'user_id' => $someOtherUser->id,
            ]);

        $response = $this->getJson(route('recipe.index'));

        $response->assertOk();
        $response->assertJsonMissing([
            'name' => $recipes[0]->name,
            'ingredients' => [
                [
                    'name' => $recipes[0]->ingredients[0]->name,
                    'amount' => $recipes[0]->ingredients[0]->pivot->amount,
                ],
            ],
            'name' => $recipes[1]->name,
            'ingredients' => [
                [
                    'name' => $recipes[1]->ingredients[0]->name,
                    'amount' => $recipes[1]->ingredients[0]->pivot->amount,
                ],
            ],
        ]);
    }
}
