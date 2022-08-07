<?php

use App\Models\Grocery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GroceriesUUIDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_grocery_id_is_a_uuid()
    {
        $grocery = Grocery::factory()->create();
        $this->assertTrue(Str::isUuid($grocery->id));
    }
}
