<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IndexCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_list_of_categories_can_be_retrieved()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $categories = Category::factory()->count(3)->create([
            'user_id' => $user->id
        ]);

        $response = $this->getJson(route('category.index'));

        $response->assertOk();
        foreach ($categories as $category) {
            $response->assertJsonFragment([
                'name' => $category->name
            ]);
        }
    }

}
