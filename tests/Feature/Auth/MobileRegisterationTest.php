<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileRegisterationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_register_and_recieve_a_token()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'device_name' => 'test device',
        ];

        $response = $this->postJson('/api/mobile/register', $data);

        $response->assertOk();
    }
}
