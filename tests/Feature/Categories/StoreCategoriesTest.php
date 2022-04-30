<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_category_can_be_created()
    {
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
        $data = $this->getCategoryData([
            'name' => 'same-category-name'
        ]);

        $this->postJson(route('category.store'), $data);
        $response = $this->postJson(route('category.store'), $data);

        $response->assertJsonValidationErrors('name');
    }

    protected function getCategoryData($merge = []): array
    {
        return array_merge([
            'name' => 'some-category'
        ], $merge);
    }
}
