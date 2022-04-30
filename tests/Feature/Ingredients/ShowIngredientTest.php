<?php

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowIngredientTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_ingredient_can_be_shown()
    {
        $ingredient = Ingredient::factory()->create();

        $response = $this->getJson(route('ingredient.show', $ingredient));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => $ingredient->name
        ]);
    }

}
