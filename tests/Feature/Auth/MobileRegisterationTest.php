<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    /** @test */
    public function a_users_password_is_encrypted()
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
        $user = User::first();
        $this->assertNotEquals($user->password, 'password');
        $this->assertTrue(Hash::check('password', $user->password));
    }
}
