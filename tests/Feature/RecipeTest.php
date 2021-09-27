<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RecipeTest extends TestCase
{

    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_recipe_can_be_stored_in_database()
    {
        $this->withoutExceptionHandling();
        $name = 'Chicken Stir Fry';
        $ingredients = [
            ['name' => 'chicken', 'amount' => 1],
            ['name' => 'stir fry vegetables', 'amount' => 2]
        ];
        $response = $this->post('/recipe', ['name' => $name, 'ingredients' => $ingredients]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('recipes', 1);
        $this->assertDatabaseCount('ingredients', 2);
        $this->assertDatabaseCount('ingredient_recipe', 2);
    }
}
