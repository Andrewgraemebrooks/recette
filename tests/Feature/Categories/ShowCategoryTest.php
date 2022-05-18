<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowCategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_category_can_be_shown()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $category = Category::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->getJson(route('category.show', $category));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $category->name
        ]);
    }

    /** @test */
    public function a_user_cannot_show_another_users_category()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $someOtherUser = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $someOtherUser->id
        ]);

        $response = $this->getJson(route('category.show', $category));

        $response->assertStatus(404);
    }

}
