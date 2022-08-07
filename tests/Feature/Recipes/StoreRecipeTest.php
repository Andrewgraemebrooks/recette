<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreRecipeTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['*']);
    }

    /** @test */
    public function a_user_can_store_a_recipe()
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
                'amount' => $ingredient->pivot->amount,
            ];
        })->toArray();
        $this->assertEquals($recipe->name, $data['name']);
        $this->assertEqualsCanonicalizing($ingredients, $data['ingredients']);
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
                    'amount' => $recipe->ingredients[0]->pivot->amount,
                ],
                [
                    'name' => $recipe->ingredients[1]->name,
                    'amount' => $recipe->ingredients[1]->pivot->amount,
                ],
            ],
        ]);
    }

    /** @test */
    public function a_name_is_required_for_recipe_creation()
    {
        $data = $this->getRecipeData(['name' => null]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('name');
        $response->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name field is required.',
                ],
            ],
        ]);
    }

    /** @test */
    public function a_list_of_ingredients_is_required_for_recipe_creation()
    {
        $data = $this->getRecipeData(['ingredients' => []]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('ingredients');
        $response->assertJsonFragment([
            'errors' => [
                'ingredients' => [
                    'The ingredients field is required.',
                ],
            ],
        ]);
    }

    /** @test */
    public function a_recipe_name_must_be_unique()
    {
        $existingRecipe = Recipe::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $data = $this->getRecipeData([
            'name' => $existingRecipe->name,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);
        $response->assertJsonValidationErrors('name');
        $response->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name has already been taken.',
                ],
            ],
        ]);
    }

    /** @test */
    public function a_recipe_name_is_only_unique_to_this_users_recipes()
    {
        $someOtherUser = User::factory()->create();
        $existingRecipe = Recipe::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);
        $this->assertDatabaseCount('recipes', 1);
        $data = $this->getRecipeData([
            'name' => $existingRecipe->name,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);
        $response->assertCreated();
        $recipesWithTheName = Recipe::where('name', $existingRecipe->name)->get();
        $this->assertEquals(2, $recipesWithTheName->count());
    }

    /** @test */
    public function the_ingredients_must_be_an_array()
    {
        $ingredientsAsString = json_encode([
            ['name' => 'some-ingredient', 'amount' => 1],
            ['name' => 'some-other-ingredient', 'amount' => 4],
        ]);
        $data = $this->getRecipeData([
            'ingredients' => $ingredientsAsString,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('ingredients');
        $response->assertJsonFragment([
            'errors' => [
                'ingredients' => [
                    'The ingredients must be an array.',
                ],
            ],
        ]);
    }

    /** @test */
    public function if_the_ingredient_already_exists_a_new_ingredient_is_not_created()
    {
        $alreadyExistingIngredient = Ingredient::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->assertDatabaseCount('ingredients', 1);
        $data = $this->getRecipeData([
            'ingredients' => [
                ['name' => $alreadyExistingIngredient->name, 'amount' => 1],
            ],
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseCount('ingredients', 1);
    }

    /** @test */
    public function if_the_existing_ingredient_was_created_by_another_user_a_new_one_is_created_for_this_user()
    {
        $someOtherUser = User::factory()->create();
        $alreadyExistingIngredient = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);
        $this->assertDatabaseCount('ingredients', 1);
        $data = $this->getRecipeData([
            'ingredients' => [
                ['name' => $alreadyExistingIngredient->name, 'amount' => 1],
            ],
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseCount('ingredients', 2);
    }

    /** @test */
    public function if_the_ingredient_already_exists_it_is_used_in_the_recipe()
    {
        $alreadyExistingIngredient = Ingredient::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $someOtherUser = User::factory()->create();
        $otherUsersIngredientWithSameName = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
            'name' => $alreadyExistingIngredient->name,
        ]);
        $data = $this->getRecipeData([
            'ingredients' => [
                ['name' => $alreadyExistingIngredient->name, 'amount' => 1],
            ],
        ]);

        $response = $this->postJson(route('recipe.store'), $data);
        $recipe = Recipe::first();
        $recipeIngredients = $recipe->ingredients;
        $this->assertTrue($recipeIngredients->contains($alreadyExistingIngredient));
        $this->assertNotTrue($recipeIngredients->contains($otherUsersIngredientWithSameName));

        $response->assertCreated();
    }

    /** @test */
    public function a_name_must_be_a_string_for_recipe_creation()
    {
        $data = $this->getRecipeData(['name' => 99999999]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('name');
        $response->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name must be a string.',
                ],
            ],
        ]);
    }

    /** @test */
    public function an_array_of_images_can_be_stored_with_a_recipe()
    {
        Storage::fake('local');
        $imageOne = Str::random(10).'.jpg';
        $imageTwo = Str::random(10).'.jpg';
        $data = $this->getRecipeData(['images' => [
            UploadedFile::fake()->image($imageOne),
            UploadedFile::fake()->image($imageTwo),
        ]]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        Storage::disk('local')->assertExists([$imageOne, $imageTwo]);
    }

    /** @test */
    public function can_store_a_recipe_without_images()
    {
        $data = $this->getRecipeData(['images' => null]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
    }

    /** @test */
    public function if_images_are_not_null_they_must_be_an_array()
    {
        $data = $this->getRecipeData(['images' => 9999999]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('images');
        $response->assertJsonFragment([
            'errors' => [
                'images' => [
                    'The images must be an array.',
                ],
            ],
        ]);
    }

    /** @test */
    public function a_recipe_can_have_a_rating()
    {
        $data = $this->getRecipeData([
            'rating' => 0,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        $recipe = Recipe::first();
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'rating' => 0,
        ]);
    }

    /** @test */
    public function a_recipe_can_be_a_string_but_must_be_a_valid_number()
    {
        $data = $this->getRecipeData([
            'rating' => '5',
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        $recipe = Recipe::first();
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'rating' => 5,
        ]);
    }

    /** @test */
    public function a_recipe_rating_must_be_a_valid_number()
    {
        $data = $this->getRecipeData([
            'rating' => false,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('rating');
        $response->assertJsonFragment([
            'errors' => [
                'rating' => [
                    'The rating must be an integer.',
                ],
            ],
        ]);
    }

    /** @test */
    public function a_recipe_rating_must_be_zero_or_greater()
    {
        $data = $this->getRecipeData([
            'rating' => -1,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('rating');
        $response->assertJsonFragment([
            'errors' => [
                'rating' => [
                    'The rating must be between 0 and 5.',
                ],
            ],
        ]);
    }

    /** @test */
    public function a_recipe_rating_must_be_five_or_less()
    {
        $data = $this->getRecipeData([
            'rating' => 6,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('rating');
        $response->assertJsonFragment([
            'errors' => [
                'rating' => [
                    'The rating must be between 0 and 5.',
                ],
            ],
        ]);
    }

    /** @test */
    public function a_recipe_can_be_assigned_to_a_category()
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $data = $this->getRecipeData([
            'category_id' => $category->id,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertCreated();
        $recipe = Recipe::first();
        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'category_id' => $category->id,
        ]);
    }

    /** @test */
    public function the_recipes_category_must_belong_to_the_user()
    {
        $someOtherUser = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);
        $data = $this->getRecipeData([
            'category_id' => $category->id,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);
        $response->assertJsonValidationErrors('category_id');
        $response->assertJsonFragment([
            'errors' => [
                'category_id' => [
                    'The selected category id is invalid.',
                ],
            ],
        ]);
    }

    /** @test */
    public function the_recipes_category_must_exist()
    {
        $randomId = Str::uuid();
        $data = $this->getRecipeData([
            'category_id' => $randomId,
        ]);

        $response = $this->postJson(route('recipe.store'), $data);

        $response->assertJsonValidationErrors('category_id');
        $response->assertJsonFragment([
            'errors' => [
                'category_id' => [
                    'The selected category id is invalid.',
                ],
            ],
        ]);
    }

    protected function getRecipeData($merge = []): array
    {
        return array_merge([
            'name' => 'some-recipe-name',
            'ingredients' => [
                ['name' => 'some-ingredient', 'amount' => 1],
                ['name' => 'some-other-ingredient', 'amount' => 4],
            ], ], $merge);
    }
}
