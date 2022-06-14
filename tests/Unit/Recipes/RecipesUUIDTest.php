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
        $recipe = Recipe::factory()->create();
        $this->assertTrue(Str::isUuid($recipe->id));
    }
}
