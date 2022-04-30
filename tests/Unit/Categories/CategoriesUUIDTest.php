<?php

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoriesUUIDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_categorys_id_is_a_uuid()
    {
        $category = Category::factory()->create();
        $this->assertTrue(Str::isUuid($category->id));
    }
}
