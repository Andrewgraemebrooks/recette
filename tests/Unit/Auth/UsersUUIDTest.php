<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;


class UsersUUIDTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_users_id_is_a_UUID()
    {
        $user = User::factory()->create();
        $this->assertTrue(Str::isUuid($user->id));
    }

}
