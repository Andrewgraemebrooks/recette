<?php

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateIngredientTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_ingredient_can_be_updated()
    {
        $ingredient = Ingredient::factory()->create();
        $newName = 'new-ingredient-name';
        $this->assertNotTrue($ingredient->name === $newName);

        $response = $this->putJson(route('ingredient.update', $ingredient), [
            'name' => $newName
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $newName
        ]);
        $ingredient->refresh();
        $this->assertTrue($ingredient->name === $newName);
        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => $newName
        ]);
    }

    /** @test */
    public function a_new_name_must_be_a_string()
    {
        $ingredient = Ingredient::factory()->create();
        $newName = 9999999;

        $response = $this->putJson(route('ingredient.update', $ingredient), [
            'name' => $newName
        ]);

        $response->assertJsonValidationErrors('name');
        $ingredient->refresh();
        $this->assertNotTrue($ingredient->name === $newName);
    }

    /** @test */
    public function a_new_name_must_be_unique()
    {
        $ingredientA = Ingredient::factory()->create();
        $ingredientB = Ingredient::factory()->create();

        $response = $this->putJson(route('ingredient.update', $ingredientA), [
            'name' => $ingredientB->name
        ]);

        $response->assertJsonValidationErrors('name');
    }

}
