<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('guest.register'));

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('guest.register.store'), [
            'email' => 'test@example.com',
            'privacy_policy' => '1',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertStatus(200)
            ->assertViewIs('user.auth.register_created');

        // Registration creates a TempUser, not a User, so user is not authenticated
        $this->assertGuest();
    }
}
