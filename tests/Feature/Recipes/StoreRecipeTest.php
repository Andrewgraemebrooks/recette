<?php

namespace Tests\Feature;

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

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
        $this->assertEqualsCanonicalizing($ingredients, $data['ingredients']);
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

    /** @test */
    public function an_array_of_images_can_be_stored_with_a_recipe()
    {
        Storage::fake('local');
        $data = $this->getRecipeData(['images' => [
            UploadedFile::fake()->image('imageOne.jpg'),
            UploadedFile::fake()->image('imageTwo.jpg')
        ]]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        Storage::disk('local')->assertExists(['imageOne.jpg','imageTwo.jpg']);
    }

    /** @test */
    public function can_store_a_recipe_without_images()
    {
        $data = $this->getRecipeData(['images' => null]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
    }

    /** @test */
    public function a_recipe_can_have_a_rating()
    {
        $data = $this->getRecipeData([
            'rating' => 0
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        $recipe = Recipe::first();
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'rating' => 0
        ]);
    }

    /** @test */
    public function a_recipe_can_be_a_string_but_must_be_a_valid_number()
    {
        $data = $this->getRecipeData([
            'rating' => '5'
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        $recipe = Recipe::first();
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'rating' => 5
        ]);
    }

    /** @test */
    public function a_recipe_rating_must_be_a_valid_number()
    {
        $data = $this->getRecipeData([
            'rating' => 'not-a-number'
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function a_recipe_rating_must_be_zero_or_greater()
    {
        $data = $this->getRecipeData([
            'rating' => -1
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function a_recipe_rating_must_be_five_or_less()
    {
        $data = $this->getRecipeData([
            'rating' => 6
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('rating');
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
