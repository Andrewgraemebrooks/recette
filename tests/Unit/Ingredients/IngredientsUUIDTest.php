<?php

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class IngredientsUUIDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_ingredient_id_is_a_uuid()
    {
        $user = User::factory()->create();
        $ingredient = Ingredient::factory()->create([
            'user_id' => $user->id
        ]);
        $this->assertTrue(Str::isUuid($ingredient->id));
    }
}
