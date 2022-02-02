<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexRecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_list_of_all_recipes_can_get_be_retrieved()
    {
        $recipes = Recipe::factory()
            ->count(2)
            ->hasAttached(
                Ingredient::factory()->count(1),
                ['amount'=> 3]
            )->create();

        $response = $this->getJson(route('recipe.index'));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $recipes[0]->name,
            'ingredients' => [
                [
                    'name' => $recipes[0]->ingredients[0]->name,
                    'amount' => $recipes[0]->ingredients[0]->pivot->amount
                ],
            ],
            'name' => $recipes[1]->name,
            'ingredients' => [
                [
                    'name' => $recipes[1]->ingredients[0]->name,
                    'amount' => $recipes[1]->ingredients[0]->pivot->amount
                ],
            ],
        ]);
    }
}
