<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_category_can_be_created()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getCategoryData();

        $response = $this->postJson(route('category.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseCount('categories', 1);
        $category = Category::first();
        $this->assertEquals($category->name, $data['name']);
    }

    /** @test */
    public function a_category_name_must_be_unique()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $categoryWithSameName = Category::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = $this->getCategoryData([
            'name' => $categoryWithSameName->name,
        ]);

        $response = $this->postJson(route('category.store'), $data);

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
    public function a_categorys_name_is_only_unique_to_this_users_categories()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $differentUser = User::factory()->create();
        $categoryWithSameName = Category::factory()->create([
            'user_id' => $differentUser->id,
        ]);
        $data = $this->getCategoryData([
            'name' => $categoryWithSameName->name,
        ]);

        $response = $this->postJson(route('category.store'), $data);

        $response->assertCreated();
        $recipesWithTheName = Category::where('name', $categoryWithSameName->name)->get();
        $this->assertEquals(2, $recipesWithTheName->count());
    }

    /** @test */
    public function a_category_name_must_be_included()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getCategoryData([
            'name' => null,
        ]);

        $response = $this->postJson(route('category.store'), $data);

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
    public function a_category_name_must_be_a_string()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getCategoryData([
            'name' => 999999,
        ]);

        $response = $this->postJson(route('category.store'), $data);

        $response->assertJsonValidationErrors('name');
        $response->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name must be a string.',
                ],
            ],
        ]);
    }

    protected function getCategoryData($merge = []): array
    {
        return array_merge([
            'name' => 'some-category',
        ], $merge);
    }
}
