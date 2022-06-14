<?php

use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelRelationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_users_recipes_can_be_accessed_via_their_model()
    {
        $user = User::factory()->create();
        Recipe::factory()->count(2)->create([
            'user_id' => $user->id
        ]);
        $this->assertNotNull($user->recipes);
        $this->assertCount(2, $user->recipes);
    }

    /** @test */
    public function a_users_ingredients_can_be_accessed_via_their_model()
    {
        $user = User::factory()->create();
        Ingredient::factory()->count(2)->create([
            'user_id' => $user->id
        ]);
        $this->assertNotNull($user->ingredients);
        $this->assertCount(2, $user->ingredients);
    }
}
