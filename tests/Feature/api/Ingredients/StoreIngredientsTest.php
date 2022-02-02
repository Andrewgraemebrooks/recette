<?php

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class StoreIngredientsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_new_ingredients()
    {
        $data = $this->getIngredientData();

        $response = $this->postJson(route('ingredient.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseCount('ingredients', 1);
        $ingredient = Ingredient::first();
        $this->assertEquals($ingredient->name, 'some-ingredient');
    }

    /** @test */
    public function a_ingredient_name_must_be_unique()
    {
        $data = $this->getIngredientData([
            'name' => 'same-ingredient-name'
        ]);

        $this->postJson(route('ingredient.store'), $data);
        $response = $this->postJson(route('ingredient.store'), $data);

        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_ingredient_id_is_a_uuid()
    {
        $data = $this->getIngredientData();

        $this->postJson(route('ingredient.store'), $data);

        $ingredient = Ingredient::first();
        $this->assertTrue(Str::isUuid($ingredient->id));
    }

    protected function getIngredientData($merge = []): array
    {
        return array_merge([
            'name' => 'some-ingredient'
        ], $merge);
    }
}
