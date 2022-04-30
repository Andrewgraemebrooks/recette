<?php

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexIngredientsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_list_of_all_ingredients_can_get_be_retrieved()
    {
        $ingredients = Ingredient::factory()->count(3)->create();

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
}
