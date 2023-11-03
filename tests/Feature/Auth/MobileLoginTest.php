<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_login_and_recieve_a_token()
    {
        User::factory()->create([
            'email' => 'some@email.com',
            'password' => bcrypt('password'),
        ]);
        $data = [
            'email' => 'some@email.com',
            'password' => 'password',
            'device_name' => 'test device',
        ];

        $response = $this->postJson('/api/mobile/login', $data);

        $response->assertOk();
    }

    /** @test */
    public function a_user_must_be_registered()
    {
        $data = [
            'email' => 'some@email.com',
            'password' => 'password',
            'device_name' => 'test device',
        ];

        $response = $this->postJson('/api/mobile/login', $data);

        $response->assertStatus(422);
    }
}
