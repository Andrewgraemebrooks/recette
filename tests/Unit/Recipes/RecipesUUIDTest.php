<?php

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

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
