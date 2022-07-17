<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_category_can_be_updated()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);
        $newName = 'new-category-name';
        $this->assertNotTrue($category->name === $newName);

        $response = $this->putJson(route('category.update', $category), [
            'name' => $newName,
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $newName,
        ]);
        $category->refresh();
        $this->assertTrue($category->name === $newName);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $newName,
        ]);
    }

    /** @test */
    public function a_new_name_must_be_a_string()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $category = Category::factory()->create([
            'user_id' => $user->id,
        ]);
        $newName = 9999999;

        $response = $this->putJson(route('category.update', $category), [
            'name' => $newName,
        ]);

        $response->assertJsonValidationErrors('name');
        $category->refresh();
        $this->assertNotTrue($category->name === $newName);
    }

    /** @test */
    public function a_new_name_must_be_unique()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $categoryA = Category::factory()->create([
            'user_id' => $user->id,
        ]);
        $categoryB = Category::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(route('category.update', $categoryA), [
            'name' => $categoryB->name,
        ]);

        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function a_categorys_name_is_only_unique_to_this_users_categories()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $differentUser = User::factory()->create();
        $categoryA = Category::factory()->create([
            'user_id' => $user->id,
        ]);
        $categoryB = Category::factory()->create([
            'user_id' => $differentUser->id,
        ]);

        $response = $this->putJson(route('category.update', $categoryA), [
            'name' => $categoryB->name,
        ]);

        $response->assertOk();
        $categoryA->refresh();
        $this->assertEquals($categoryB->name, $categoryA->name);
    }

    /** @test */
    public function a_user_cannot_update_another_users_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $someOtherUser->id,
        ]);

        $response = $this->putJson(route('category.update', $category), [
            'name' => 'some-new-name',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('ingredients', [
            'name' => 'some-new-name',
        ]);
    }
}
