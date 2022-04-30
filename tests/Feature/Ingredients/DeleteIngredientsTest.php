<?php

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteIngredientsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_ingredient_can_be_deleted()
    {
        $ingredient = Ingredient::factory()->create();

        $response = $this->deleteJson(route('ingredient.destroy', $ingredient));

        $response->assertNoContent();
        $this->assertDatabaseMissing('ingredients', [
            'id' => $ingredient->id
        ]);
    }
}
