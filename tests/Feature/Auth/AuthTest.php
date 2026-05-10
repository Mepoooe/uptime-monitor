<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_is_accessible(): void
    {
        $this->get(route('register'))
            ->assertStatus(200);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $this->postWithCsrf('register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postWithCsrf('register', [
            'name' => 'Another User',
            'email' => 'taken@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ])
            ->assertSessionHasErrors('email');
    }

    public function test_registration_fails_when_passwords_do_not_match(): void
    {
        $this->postWithCsrf('register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'different',
        ])
            ->assertSessionHasErrors('password');
    }

    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))
            ->assertStatus(200);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->postWithCsrf('login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $this->postWithCsrf('login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
