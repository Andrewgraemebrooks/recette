<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateRecipeTest extends TestCase
{
    use RefreshDatabase;

    private $recipe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recipe = Recipe::factory()
            ->hasAttached(
                Ingredient::factory()->count(2),
                ['amount' => 3]
            )->create();
    }

    /** @test */
    public function a_recipe_name_can_be_updated()
    {
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => 'new-ingredient-name'
        ]);

        $response->assertOk();
        $this->recipe->refresh();
        $this->assertEquals($this->recipe->name, 'new-ingredient-name');
    }

    /** @test */
    public function the_name_must_be_a_string_for_recipe_update()
    {
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => 999999
        ]);

        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_recipe_name_must_be_unique()
    {
        $differentRecipe = Recipe::factory()->create();

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => $differentRecipe->name
        ]);

        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function the_name_can_be_null_on_an_update()
    {
        $originalRecipeName = $this->recipe->name;
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => null
        ]);

        $response->assertOk();
        $this->recipe->refresh();
        $this->assertTrue($this->recipe->name === $originalRecipeName);
    }

    /** @test */
    public function a_recipes_ingredients_can_be_updated()
    {
        $newIngredients = [
            ['name' => 'updated-ingredient', 'amount' => 1],
            ['name' => 'some-other-updated-ingredient', 'amount' => 4],
        ];

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => $newIngredients
        ]);

        $response->assertOk();
        $responseIngredients = $response->json('data')['ingredients'];
        $this->recipe->refresh();
        $this->assertEqualsCanonicalizing($newIngredients, $responseIngredients);
    }

    /** @test */
    public function if_the_ingredient_already_exists_a_new_ingredient_is_not_created()
    {
        $alreadyExistingIngredient = Ingredient::factory()->create();
        $existingIngredientCount = Ingredient::all()->count();

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => [
                ['name' => $alreadyExistingIngredient->name, 'amount' => 1]
            ]
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('ingredients', $existingIngredientCount);
    }


    /** @test */
    public function the_ingredients_can_be_null()
    {
        $originalRecipeIngredients = collect($this->recipe->ingredients)->map(function ($ingredient) {
            return [
                'name' => $ingredient->name,
                'amount' => $ingredient->pivot->amount
            ];
        })->toArray();
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => null
        ]);

        $response->assertOk();
        $responseIngredients = $response->json('data')['ingredients'];
        $this->recipe->refresh();
        $this->assertEqualsCanonicalizing($originalRecipeIngredients, $responseIngredients);
    }

    /** @test */
    public function the_ingredients_must_be_an_array()
    {
        $newIngredientsAsString = json_encode([
            ['name' => 'some-ingredient', 'amount' => 1],
            ['name' => 'some-other-ingredient', 'amount' => 4],
        ]);
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => $newIngredientsAsString
        ]);

        $response->assertJsonValidationErrors('ingredients');
    }

    /** @test */
    public function an_array_of_images_can_be_used_with_a_recipe_update()
    {
        $this->withoutExceptionHandling();
        Storage::fake('local');
        $data = ['images' => [
            UploadedFile::fake()->image('imageOne.jpg'),
            UploadedFile::fake()->image('imageTwo.jpg')
        ]];

        $response = $this->putJson(route('recipe.update', $this->recipe), $data);

        $response->assertOk();
        Storage::disk('local')->assertExists(['imageOne.jpg', 'imageTwo.jpg']);
    }

    /** @test */
    public function can_update_a_recipe_without_images()
    {
        $data = ['images' => null];

        $response = $this->putJson(route('recipe.update', $this->recipe), $data);

        $response->assertOk();
    }

    /** @test */
    public function a_recipe_can_be_updated_with_a_rating()
    {
        $newRating = 3;
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'rating' => $newRating
        ]);

        $response->assertOk();
        $recipe = Recipe::first();
        $this->assertEquals($newRating, $recipe->rating);
    }

    /** @test */
    public function a_recipe_can_be_a_string_but_must_be_a_valid_number()
    {
        $newRating = '5';

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'rating' => $newRating
        ]);

        $response->assertOk();
        $recipe = Recipe::first();
        $this->assertEquals($newRating, $recipe->rating);
    }

    /** @test */
    public function a_recipe_rating_must_be_a_valid_number()
    {
        $newRating = 'not-a-number';

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'rating' => $newRating
        ]);

        $response->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function a_recipe_rating_must_be_zero_or_greater()
    {
        $newRating = -1;

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'rating' => $newRating
        ]);

        $response->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function a_recipe_rating_must_be_five_or_less()
    {
        $newRating = 6;

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'rating' => $newRating
        ]);

        $response->assertJsonValidationErrors('rating');
    }
}
