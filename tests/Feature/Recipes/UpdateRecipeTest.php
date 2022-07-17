<?php

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

class UpdateRecipeTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $recipe;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['*']);
        $ingredients = Ingredient::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);
        $this->recipe = Recipe::factory()
            ->hasAttached(
                $ingredients,
                ['amount' => 3]
            )->create([
                'user_id' => $this->user->id,
            ]);
    }

    /** @test */
    public function a_user_can_update_their_recipe()
    {
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => 'new-ingredient-name',
        ]);

        $response->assertOk();
        $this->recipe->refresh();
        $this->assertEquals($this->recipe->name, 'new-ingredient-name');
    }

    /** @test */
    public function a_user_cannot_update_another_users_recipe()
    {
        $someOtherUser = User::factory()->create();
        $someOtherUsersRecipe = Recipe::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);

        $response = $this->putJson(route('recipe.update', $someOtherUsersRecipe), [
            'name' => 'new-ingredient-name',
        ]);

        $response->assertStatus(404);
        $this->recipe->refresh();
        $this->assertNotEquals($someOtherUsersRecipe->name, 'new-ingredient-name');
    }

    /** @test */
    public function a_recipe_name_can_be_updated()
    {
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => 'new-ingredient-name',
        ]);

        $response->assertOk();
        $this->recipe->refresh();
        $this->assertEquals($this->recipe->name, 'new-ingredient-name');
    }

    /** @test */
    public function the_name_must_be_a_string_for_recipe_update()
    {
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => 999999,
        ]);

        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_recipe_name_must_be_unique()
    {
        $differentRecipe = Recipe::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => $differentRecipe->name,
        ]);

        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_recipe_name_is_only_unique_to_this_users_recipes()
    {
        $differentUser = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'user_id' => $differentUser->id,
        ]);

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'name' => $recipe->name,
        ]);

        $response->assertOk();
        $this->assertCount(2, Recipe::where('name', $recipe->name)->get());
    }

    /** @test */
    public function a_recipes_ingredients_can_be_updated()
    {
        $newIngredients = [
            ['name' => 'updated-ingredient', 'amount' => 1],
            ['name' => 'some-other-updated-ingredient', 'amount' => 4],
        ];

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => $newIngredients,
        ]);

        $response->assertOk();
        $responseIngredients = $response->json('data')['ingredients'];
        $this->recipe->refresh();
        $this->assertEqualsCanonicalizing($newIngredients, $responseIngredients);
    }

    /** @test */
    public function if_the_ingredient_already_exists_a_new_ingredient_is_not_created()
    {
        $alreadyExistingIngredient = Ingredient::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $existingIngredientCount = Ingredient::all()->count();

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => [
                ['name' => $alreadyExistingIngredient->name, 'amount' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('ingredients', $existingIngredientCount);
    }

    /** @test */
    public function if_the_existing_ingredient_was_created_by_another_user_a_new_one_is_created_for_this_user()
    {
        $someOtherUser = User::factory()->create();
        $alreadyExistingIngredient = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);
        $existingIngredientCount = Ingredient::all()->count();

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => [
                ['name' => $alreadyExistingIngredient->name, 'amount' => 1],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('ingredients', $existingIngredientCount + 1);
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
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => [
                ['name' => $alreadyExistingIngredient->name, 'amount' => 1],
            ],
        ]);

        $recipeIngredients = $this->recipe->ingredients;
        $this->assertTrue($recipeIngredients->contains($alreadyExistingIngredient));
        $this->assertNotTrue($recipeIngredients->contains($otherUsersIngredientWithSameName));

        $response->assertOk();
    }

    /** @test */
    public function the_ingredients_must_be_an_array()
    {
        $newIngredientsAsString = json_encode([
            ['name' => 'some-ingredient', 'amount' => 1],
            ['name' => 'some-other-ingredient', 'amount' => 4],
        ]);
        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'ingredients' => $newIngredientsAsString,
        ]);

        $response->assertJsonValidationErrors('ingredients');
    }

    /** @test */
    public function an_array_of_images_can_be_used_with_a_recipe_update()
    {
        Storage::fake('local');
        $data = ['images' => [
            UploadedFile::fake()->image('imageOne.jpg'),
            UploadedFile::fake()->image('imageTwo.jpg'),
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
            'rating' => $newRating,
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
            'rating' => $newRating,
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
            'rating' => $newRating,
        ]);

        $response->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function a_recipe_rating_must_be_zero_or_greater()
    {
        $newRating = -1;

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'rating' => $newRating,
        ]);

        $response->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function a_recipe_rating_must_be_five_or_less()
    {
        $newRating = 6;

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'rating' => $newRating,
        ]);

        $response->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function a_recipes_category_can_be_updated()
    {
        $newCategory = Category::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'category_id' => $newCategory->id,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('recipes', [
            'id' => $this->recipe->id,
            'category_id' => $newCategory->id,
        ]);
    }

    /** @test */
    public function the_recipes_category_must_exist()
    {
        $randomId = Str::uuid();

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'category_id' => $randomId,
        ]);

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
    public function the_recipes_category_must_belong_to_the_user()
    {
        $someOtherUser = User::factory()->create();
        $newCategory = Category::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);

        $response = $this->putJson(route('recipe.update', $this->recipe), [
            'category_id' => $newCategory->id,
        ]);

        $response->assertJsonValidationErrors('category_id');
    }
}
