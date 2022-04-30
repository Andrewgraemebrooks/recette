<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexCategoriesTest extends TestCase
{
    use RefreshDatabase;
    
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
