<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_category_can_be_updated()
    {
        $category = Category::factory()->create();
        $newName = 'new-category-name';
        $this->assertNotTrue($category->name === $newName);

        $response = $this->putJson(route('category.update', $category), [
            'name' => $newName
        ]);

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $newName
        ]);
        $category->refresh();
        $this->assertTrue($category->name === $newName);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $newName
        ]);
    }

    /** @test */
    public function a_new_name_must_be_a_string()
    {
        $category = Category::factory()->create();
        $newName = 9999999;

        $response = $this->putJson(route('category.update', $category), [
            'name' => $newName
        ]);

        $response->assertJsonValidationErrors('name');
        $category->refresh();
        $this->assertNotTrue($category->name === $newName);
    }

    /** @test */
    public function a_new_name_must_be_unique()
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $response = $this->putJson(route('category.update', $categoryA), [
            'name' => $categoryB->name
        ]);

        $response->assertJsonValidationErrors('name');
    }
}
