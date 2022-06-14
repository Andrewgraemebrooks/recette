<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_category_can_be_deleted()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $category = Category::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->deleteJson(route('category.destroy', $category));

        $response->assertNoContent();
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

}
