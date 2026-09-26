<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class AuthExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_exposes_accessible_authentication_controls(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('for="email"', false)
            ->assertSee('for="password"', false)
            ->assertSee('autocomplete="email"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('name="remember"', false)
            ->assertSee('Ghi nhớ đăng nhập')
            ->assertSee('data-password-toggle', false)
            ->assertSee('aria-hidden="true"', false)
            ->assertDontSee('aria-label="Chú chó đang ăn"', false);
    }

    public function test_login_uses_branded_fonts_and_repeats_animation(): void
    {
        $css = file_get_contents(public_path('auth.css'));
        $animation = file_get_contents(resource_path('js/auth-lottie.js'));

        $this->assertStringContainsString('font-family:"Be Vietnam Pro"', $css);
        $this->assertStringContainsString('font:700 42px/1.08 "Bricolage Grotesque"', $css);
        $this->assertStringContainsString('loop: true', $animation);
    }

    public function test_failed_login_preserves_email_and_renders_linked_inline_error(): void
    {
        $email = 'customer@example.com';

        $this->from(route('login'))->post(route('login'), [
            'email' => $email,
            'password' => 'incorrect-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors('email');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('id="login-error-summary"', false)
            ->assertSee('href="#email"', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('aria-describedby="email-error"', false)
            ->assertSee('value="'.$email.'"', false)
            ->assertSee('Email hoặc mật khẩu chưa đúng.');
    }

    public function test_social_login_buttons_only_appear_when_the_provider_is_configured(): void
    {
        config()->set('services.google', ['client_id' => 'google-id', 'client_secret' => 'google-secret', 'redirect' => null]);
        config()->set('services.facebook', ['client_id' => null, 'client_secret' => null, 'redirect' => null]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Đăng nhập với Google')
            ->assertDontSee('Đăng nhập với Facebook');

        config()->set('services.google.client_id', null);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('Đăng nhập với Google')
            ->assertDontSee('Hoặc</span>', false);
    }

    public function test_repeated_successful_logins_are_not_rate_limited(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 7) as $attempt) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect(route('home'));
            $this->assertAuthenticatedAs($user);
            $this->post(route('logout'));
        }
    }

    public function test_five_wrong_passwords_lock_that_email_with_an_inline_message(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->from(route('login'))->post(route('login'), ['email' => $user->email, 'password' => 'wrong-password'])
                ->assertRedirect(route('login'))
                ->assertSessionHasErrors(['email' => 'Email hoặc mật khẩu chưa đúng.']);
        }

        $this->from(route('login'))->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('quá nhiều lần', session('errors')->first('email'));
        $this->assertGuest();

        $other = User::factory()->create();
        $this->post(route('login'), ['email' => $other->email, 'password' => 'password'])
            ->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($other);
    }

    public function test_social_login_failure_does_not_expose_provider_exception_details(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->once()->andReturnSelf();
        $provider->shouldReceive('user')->once()->andThrow(new RuntimeException('private provider detail'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('social.callback', ['provider' => 'google']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('social')
            ->assertSessionDoesntHaveErrors('error');
    }
}
