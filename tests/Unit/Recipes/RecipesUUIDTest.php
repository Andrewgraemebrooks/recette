<?php

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RecipesUUIDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function the_recipe_id_is_a_uuid()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create([
            'user_id' => $user->id
        ]);
        $this->assertTrue(Str::isUuid($recipe->id));
    }
}
