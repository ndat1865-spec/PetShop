<?php

namespace Tests\Feature;

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
