<?php

use App\Http\Resources\IngredientResource;
use App\Http\Resources\RecipeIngredientResource;
use App\Http\Resources\RecipeResource;
use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeResourceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_resource_returns_the_correct_information()
    {
        $ingredient = Ingredient::factory()->create();
        $recipe = Recipe::factory()
            ->hasAttached(
                $ingredient,
                ['amount'=> 3]
            )->create();

        $ingredientResource = (new RecipeIngredientResource($ingredient));
        $actualData = (new RecipeResource($recipe))->jsonSerialize();

        // $ingredients = (new RecipeIngredientResource())
        $expectedData = [
            'name' => $recipe->name,
            'ingredients' => $ingredientResource
        ];
        $this->assertEquals($expectedData, $actualData);

    }
}
