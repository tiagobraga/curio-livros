<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Mail\ResetPasswordMail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_forgot_password_page(): void
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
        $response->assertSee(__('Esqueceu sua senha?'));
    }

    public function test_password_reset_request_sends_email(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'testreset@bibliio.com.br',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'testreset@bibliio.com.br',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        
        Mail::assertSent(ResetPasswordMail::class, function ($mail) use ($user) {
            return $mail->hasTo('testreset@bibliio.com.br') && 
                   $mail->user->id === $user->id &&
                   str_contains($mail->url, 'reset-password/');
        });
    }

    public function test_guest_can_view_reset_password_page(): void
    {
        $user = User::factory()->create([
            'email' => 'testreset@bibliio.com.br',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));
        $response->assertStatus(200);
        $response->assertSee(__('Redefinir Senha'));
    }

    public function test_guest_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'testreset@bibliio.com.br',
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'testreset@bibliio.com.br',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
