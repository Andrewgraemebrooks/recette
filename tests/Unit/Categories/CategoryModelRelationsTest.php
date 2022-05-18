<?php

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_categorys_user_can_be_accessed_via_their_model()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id
        ]);
        $this->assertNotNull($category->user);
        $this->assertEquals($user->id, $category->user->id);
    }
}
