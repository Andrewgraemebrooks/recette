<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_category_can_be_deleted()
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson(route('category.destroy', $category));

        $response->assertNoContent();
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

}
