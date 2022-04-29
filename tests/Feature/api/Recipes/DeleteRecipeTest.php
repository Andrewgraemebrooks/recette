<?php

use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteRecipeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_recipe_can_be_deleted()
    {
        $recipe = Recipe::factory()->create();

        $response = $this->deleteJson(route('recipe.destroy', $recipe));

        $response->assertNoContent();
        $this->assertDatabaseMissing('recipes', [
            'id' => $recipe->id
        ]);
    }
}
