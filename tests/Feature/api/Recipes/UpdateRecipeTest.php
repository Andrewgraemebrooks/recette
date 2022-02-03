<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateRecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recipe_can_be_updated()
    {
        $recipe = Recipe::factory()
            ->hasAttached(
                Ingredient::factory()->count(2),
                ['amount'=> 3]
            )->create();

        $response = $this->putJson(route('recipe.update', $recipe->id), [
            'name' => 'new-ingredient-name'
        ]);

        $response->assertOk();
        $recipe->refresh();
        $this->assertEquals($recipe->name, 'new-ingredient-name');
    }
}
