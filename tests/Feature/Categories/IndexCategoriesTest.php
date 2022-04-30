<?php

use App\Models\Category;
use Tests\TestCase;

class IndexCategoriesTest extends TestCase
{
    /** @test */
    public function a_list_of_categories_can_be_retrieved()
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->getJson(route('category.index'));

        $response->assertOk();
        foreach ($categories as $category) {
            $response->assertJsonFragment([
                'name' => $category->name
            ]);
        }
    }

}
