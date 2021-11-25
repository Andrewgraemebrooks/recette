<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function a_recipe_can_be_stored_in_database()
    {
        $data = $this->getRecipeData();
        $response = $this->postJson('/recipe', $data);
        $response->assertStatus(200);
        $this->assertDatabaseCount('recipes', 1);
        $this->assertDatabaseCount('ingredients', 2);
        $this->assertDatabaseCount('ingredient_recipe', 2);
    }

    /** @test */
    public function a_name_is_required_for_recipe_creation()
    {
        $data = $this->getRecipeData(['name' => '']);
        $response = $this->postJson('/recipe', $data);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_list_of_ingredients_is_required_for_recipe_creation()
    {
        $data = $this->getRecipeData(['ingredients' => []]);
        $response = $this->postJson('/recipe', $data);
        $response->assertJsonValidationErrors('ingredients');
    }

    /** @test */
    public function a_recipe_name_must_be_unique()
    {
        $data = $this->getRecipeData();
        $this->postJson('/recipe', $data);
        $response = $this->postJson('/recipe', $data);
        $response->assertJsonValidationErrors('name');
    }

    protected function getRecipeData($merge = []): array
    {
        return array_merge([
            'name' => 'some-recipe-name',
            'ingredients' => [
                ['name' => 'some-ingredient', 'amount' => 1],
                ['name' => 'some-other-ingredient', 'amount' => 1],
        ]], $merge);
    }
}
