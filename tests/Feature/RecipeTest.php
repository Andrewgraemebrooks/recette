<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Tests whether or not a recipe can be stored in the database
     *
     * @return void
     */
    public function test_a_recipe_can_be_stored_in_database()
    {
        $this->withoutExceptionHandling();
        $name = 'Chicken Stir Fry';
        $ingredients = [
            ['name' => 'chicken', 'amount' => 1],
            ['name' => 'stir fry vegetables', 'amount' => 2],
        ];
        $response = $this->post('/recipe', ['name' => $name, 'ingredients' => $ingredients]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('recipes', 1);
        $this->assertDatabaseCount('ingredients', 2);
        $this->assertDatabaseCount('ingredient_recipe', 2);
    }

    /**
     * Tests whether or not a name is required for the creation of a recipe
     *
     * @return void
     */
    public function test_a_name_is_required_for_recipe_creation()
    {
        $name = '';
        $ingredients = [
            ['name' => 'chicken', 'amount' => 1],
            ['name' => 'stir fry vegetables', 'amount' => 2],
        ];
        $response = $this->post('/recipe', ['name' => $name, 'ingredients' => $ingredients]);
        $response->assertSessionHasErrors('name');
    }

    /**
     * Tests whether or not a list of ingredients is required for the creation of a recipe
     *
     * @return void
     */
    public function test_a_list_of_ingredients_is_required_for_recipe_creation()
    {
        $name = 'Chicken Stir Fry';
        $ingredients = [];
        $response = $this->post('/recipe', ['name' => $name, 'ingredients' => $ingredients]);
        $response->assertSessionHasErrors('ingredients');
    }

    /**
     * Test whether or not a recipe name has to be unique for the creation of a recipe
     *
     * @return void
     */
    public function test_a_recipe_name_must_be_unique()
    {
        $firstName = 'Chicken Stir Fry';
        $firstIngredients = [
            ['name' => 'chicken', 'amount' => 1],
            ['name' => 'stir fry vegetables', 'amount' => 2],
        ];
        $this->post('/recipe', ['name' => $firstName, 'ingredients' => $firstIngredients]);

        $secondName = 'Chicken Stir Fry';
        $secondIngredients = [
            ['name' => 'Beef', 'amount' => 3],
            ['name' => 'Stew', 'amount' => 4],
        ];
        $secondResponse = $this->post('/recipe', ['name' => $secondName, 'ingredients' => $secondIngredients]);
        $secondResponse->assertSessionHasErrors('name');
    }
}
