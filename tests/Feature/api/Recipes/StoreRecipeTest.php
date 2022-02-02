<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Str;

class StoreRecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recipe_can_be_stored_in_database()
    {
        $data = $this->getRecipeData();

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseCount('recipes', 1);
        $this->assertDatabaseCount('ingredients', 2);
        $this->assertDatabaseCount('ingredient_recipe', 2);
        $recipe = Recipe::first();
        $ingredients = collect($recipe->ingredients)->map(function ($ingredient) {
            return [
                'name' => $ingredient->name,
                'amount' => $ingredient->pivot->amount
            ];
        })->toArray();
        $this->assertEquals($recipe->name, $data['name']);
        $this->assertArrayHasKey('name', $ingredients[0]);
        $this->assertEqualsCanonicalizing($data['ingredients'], $ingredients);
    }

    /** @test */
    public function the_correct_recipe_information_is_returned_on_successful_creation()
    {
        $data = $this->getRecipeData();

        $response = $this->postJson(route('recipe.store'), $data);

        $recipe = Recipe::first();
        $response->assertJsonFragment([
            'name' => $recipe->name,
            'ingredients' => [
                [
                    'name' => $recipe->ingredients[0]->name,
                    'amount' => $recipe->ingredients[0]->pivot->amount
                ],
                [
                    'name' => $recipe->ingredients[1]->name,
                    'amount' => $recipe->ingredients[1]->pivot->amount
                ],
            ]
        ]);
    }


    /** @test */
    public function a_name_is_required_for_recipe_creation()
    {
        $data = $this->getRecipeData(['name' => null]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_list_of_ingredients_is_required_for_recipe_creation()
    {
        $data = $this->getRecipeData(['ingredients' => []]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('ingredients');
    }

    /** @test */
    public function a_recipe_name_must_be_unique()
    {
        $data = $this->getRecipeData();

        $this->postJson(route('recipe.store'), $data);

        $response = $this->postJson(route('recipe.store'), $data);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function the_recipe_id_is_a_uuid()
    {
        $data = $this->getRecipeData();

        $this->postJson(route('recipe.store'), $data);

        $recipe = Recipe::first();
        $this->assertTrue(Str::isUuid($recipe->id));
    }

    /** @test */
    public function the_ingredients_must_be_an_array()
    {
        $ingredients = json_encode([
            ['name' => 'some-ingredient', 'amount' => 1],
            ['name' => 'some-other-ingredient', 'amount' => 4],
        ]);
        $data = $this->getRecipeData([
            'ingredients' => $ingredients
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('ingredients');
    }

    /** @test */
    public function a_name_must_be_a_string_for_recipe_creation()
    {
        $data = $this->getRecipeData(['name' => 99999999]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('name');
    }

    protected function getRecipeData($merge = []): array
    {
        return array_merge([
            'name' => 'some-recipe-name',
            'ingredients' => [
                ['name' => 'some-ingredient', 'amount' => 1],
                ['name' => 'some-other-ingredient', 'amount' => 4],
        ]], $merge);
    }
}
