<?php

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class IngredientsUUIDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_ingredient_id_is_a_uuid()
    {
        $ingredient = Ingredient::factory()->create();
        $this->assertTrue(Str::isUuid($ingredient->id));
    }
}
