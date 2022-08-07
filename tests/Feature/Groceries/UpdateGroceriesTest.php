<?php

use App\Models\Grocery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateGroceriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_update_a_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $grocery = Grocery::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = $this->getGroceryData();

        $response = $this->putJson(route('grocery.update', $grocery), $data);

        $response->assertOk();
        $this->assertDatabaseHas('groceries', [
            'name' => $data['name'],
            'amount' => $data['amount'],
        ]);
    }

    /** @test */
    public function updating_a_grocery_requires_a_name()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $grocery = Grocery::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = $this->getGroceryData([
            'name' => null,
        ]);

        $response = $this->putJson(route('grocery.update', $grocery), $data);

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
    public function updating_a_grocery_requires_an_amount()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $grocery = Grocery::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = $this->getGroceryData([
            'amount' => null,
        ]);

        $response = $this->putJson(route('grocery.update', $grocery), $data);

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
        $grocery = Grocery::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = $this->getGroceryData([
            'name' => true,
        ]);

        $response = $this->putJson(route('grocery.update', $grocery), $data);

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
        $grocery = Grocery::factory()->create([
            'user_id' => $user->id,
        ]);
        $data = $this->getGroceryData([
            'amount' => 'not-an-integer',
        ]);

        $response = $this->putJson(route('grocery.update', $grocery), $data);

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
    public function the_grocery_must_belong_to_the_user_updating_the_grocery()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        $otherUser = User::factory()->create();
        $grocery = Grocery::factory()->create([
            'user_id' => $otherUser->id,
        ]);
        $data = $this->getGroceryData();

        $response = $this->putJson(route('grocery.update', $grocery), $data);
        $response->assertNotFound();

        $this->assertDatabaseMissing('groceries', [
            'name' => $data['name'],
            'amount' => $data['amount'],
        ]);
    }

    protected function getGroceryData($merge = []): array
    {
        return array_merge([
            'name' => 'some-new-grocery-name',
            'amount' => rand(0, 15),
        ], $merge);
    }
}
