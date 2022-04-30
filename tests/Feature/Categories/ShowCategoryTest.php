<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_category_can_be_shown()
    {
        $category = Category::factory()->create();

        $response = $this->getJson(route('category.show', $category));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $category->name
        ]);
    }
}
