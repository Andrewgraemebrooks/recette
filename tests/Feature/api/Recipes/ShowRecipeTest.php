<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowRecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recipe_can_be_shown()
    {
        $recipe = Recipe::factory()
            ->hasAttached(
                Ingredient::factory(),
                ['amount'=> 1]
            )->create();

        $response = $this->getJson(route('recipe.show', $recipe));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $recipe->name,
            'ingredients' => [
                [
                    'name' => $recipe->ingredients[0]->name,
                    'amount' => $recipe->ingredients[0]->pivot->amount
                ]
            ]
        ]);
    }

}
