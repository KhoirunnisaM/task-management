<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_register_page(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(__('auth.register_title'))
            ->assertSee(__('auth.have_account'))
            ->assertSee(route('login'));
    }

    public function test_login_page_links_to_the_register_page(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('auth.no_account'))
            ->assertSee(route('register'));
    }

    public function test_guest_can_register_and_is_redirected_to_login(): void
    {
        $this->post(route('register'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', __('auth.registered'));

        $this->assertGuest();

        $user = User::query()->where('email', 'budi@example.com')->sole();

        $this->assertSame('Budi Santoso', $user->name);
        $this->assertSame(UserRole::Member, $user->role);
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_registration_ignores_a_role_sent_by_the_user(): void
    {
        $this->post(route('register'), [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => UserRole::Admin->value,
        ])->assertRedirect(route('login'));

        $this->assertSame(
            UserRole::Member,
            User::query()->where('email', 'budi@example.com')->sole()->role,
        );
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'budi@example.com']);

        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(1, User::query()->where('email', 'budi@example.com')->count());
    }

    public function test_password_must_be_at_least_eight_characters(): void
    {
        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'password' => 'pendek',
                'password_confirmation' => 'pendek',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->from(route('register'))
            ->post(route('register'), [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia456',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'budi@example.com']);
    }

    public function test_authenticated_user_cannot_open_the_register_page(): void
    {
        $this->actingAs(User::factory()->member()->create())
            ->get(route('register'))
            ->assertRedirect(route('dashboard'));
    }
}
