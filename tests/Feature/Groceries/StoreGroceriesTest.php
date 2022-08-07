<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StoreGroceriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_create_a_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getGroceryData();

        $response = $this->postJson(route('grocery.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('groceries', [
            'name' => $data['name'],
            'amount' => $data['amount'],
        ]);
    }

    /** @test */
    public function creating_a_grocery_requires_a_name()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getGroceryData([
            'name' => null,
        ]);

        $response = $this->postJson(route('grocery.store'), $data);

        $response->assertJsonValidationErrors('name');
        $response->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name field is required.',
                ],
            ],
        ]);
    }

    /** @test */
    public function creating_a_grocery_requires_an_amount()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getGroceryData([
            'amount' => null,
        ]);

        $response = $this->postJson(route('grocery.store'), $data);

        $response->assertJsonValidationErrors('amount');
        $response->assertJsonFragment([
            'errors' => [
                'amount' => [
                    'The amount field is required.',
                ],
            ],
        ]);
    }

    /** @test */
    public function the_grocery_name_must_be_a_string()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getGroceryData([
            'name' => true,
        ]);

        $response = $this->postJson(route('grocery.store'), $data);

        $response->assertJsonValidationErrors('name');
        $response->assertJsonFragment([
            'errors' => [
                'name' => [
                    'The name must be a string.',
                ],
            ],
        ]);
    }

    /** @test */
    public function the_grocery_amount_must_be_an_integer()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getGroceryData([
            'amount' => 'not-an-integer',
        ]);

        $response = $this->postJson(route('grocery.store'), $data);

        $response->assertJsonValidationErrors('amount');
        $response->assertJsonFragment([
            'errors' => [
                'amount' => [
                    'The amount must be an integer.',
                ],
            ],
        ]);
    }

    /** @test */
    public function the_grocery_belongs_to_the_user_creating_the_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $data = $this->getGroceryData();

        $response = $this->postJson(route('grocery.store'), $data);
        $response->assertCreated();

        $this->assertDatabaseHas('groceries', [
            'name' => $data['name'],
            'user_id' => $user->id,
        ]);
    }

    protected function getGroceryData($merge = []): array
    {
        return array_merge([
            'name' => 'some-grocery',
            'amount' => rand(0, 15),
        ], $merge);
    }
}
