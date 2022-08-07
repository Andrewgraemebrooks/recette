<?php

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreIngredientsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_new_ingredients()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
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
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $existingIngredient = Ingredient::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = $this->getIngredientData([
            'name' => $existingIngredient->name,
        ]);

        $response = $this->postJson(route('ingredient.store'), $data);

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
        $existingIngredient = Ingredient::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);
        $data = $this->getIngredientData([
            'name' => $existingIngredient->name,
        ]);

        $response = $this->postJson(route('ingredient.store'), $data);

        $response->assertCreated();
        $ingredientsWithTheName = Ingredient::where('name', $existingIngredient->name)->get();
        $this->assertTrue($ingredientsWithTheName->count() === 2);
    }

    /** @test */
    public function a_ingredient_name_must_be_a_string()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getIngredientData([
            'name' => 99999999,
        ]);

        $response = $this->postJson(route('ingredient.store'), $data);

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
    public function an_ingredient_can_have_images_attached_to_it()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $imageOne = Str::random(10).'.jpg';
        $imageTwo = Str::random(10).'.jpg';
        $data = $this->getIngredientData([
            'images' => [
                UploadedFile::fake()->image($imageOne),
                UploadedFile::fake()->image($imageTwo),
            ],
        ]);

        $response = $this->postJson(route('ingredient.store'), $data);
        $response->assertCreated();
        Storage::disk('local')->assertExists([$imageOne, $imageTwo]);
    }

    protected function getIngredientData($merge = []): array
    {
        return array_merge([
            'name' => 'some-ingredient',
        ], $merge);
    }
}
