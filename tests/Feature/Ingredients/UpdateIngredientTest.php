<?php

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateIngredientTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_ingredient_can_be_updated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);
        $newName = 'new-ingredient-name';
        $this->assertNotTrue($ingredient->name === $newName);

        $response = $this->putJson(route('ingredient.update', $ingredient), [
            'name' => $newName,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $newName,
        ]);
        $ingredient->refresh();
        $this->assertTrue($ingredient->name === $newName);
        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'name' => $newName,
        ]);
    }

    /** @test */
    public function a_new_name_must_be_a_string()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);
        $newName = 9999999;

        $response = $this->putJson(route('ingredient.update', $ingredient), [
            'name' => $newName,
        ]);

        $response->assertJsonValidationErrors('name');
        $response->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name must be a string.',
                ],
            ],
        ]);
        $ingredient->refresh();
        $this->assertNotTrue($ingredient->name === $newName);
    }

    /** @test */
    public function a_new_name_must_be_unique()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredientA = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);
        $ingredientB = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(route('ingredient.update', $ingredientA), [
            'name' => $ingredientB->name,
        ]);

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
    public function a_ingredient_name_is_only_unique_to_this_users_ingredients()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $ingredientA = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);
        $ingredientB = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);

        $response = $this->putJson(route('ingredient.update', $ingredientA), [
            'name' => $ingredientB->name,
        ]);

        $response->assertOk();
        $ingredientsWithTheName = Ingredient::where('name', $ingredientB->name)->get();
        $this->assertEquals(2, $ingredientsWithTheName->count());
    }

    /** @test */
    public function a_user_cannot_update_another_users_recipe()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);

        $response = $this->putJson(route('ingredient.update', $ingredient), [
            'name' => 'some-new-name',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('ingredients', [
            'name' => 'some-new-name',
        ]);
    }

    /** @test */
    public function an_ingredient_can_have_images_attached_to_it()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);
        $imageOne = Str::random(10).'.jpg';
        $imageTwo = Str::random(10).'.jpg';
        $data = [
            'name' => 'some-name',
            'images' => [
                UploadedFile::fake()->image($imageOne),
                UploadedFile::fake()->image($imageTwo),
            ],
        ];

        $response = $this->putJson(route('ingredient.update', $ingredient), $data);
        $response->assertOk();
        Storage::disk('local')->assertExists([$imageOne, $imageTwo]);
    }
}
