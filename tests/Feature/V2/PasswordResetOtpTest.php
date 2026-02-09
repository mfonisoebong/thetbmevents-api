<?php

namespace Tests\Feature\V2;

use App\Mail\OtpCode;
use App\Mail\PasswordChangedMail;
use App\Models\OtpVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $email): User
    {
        return User::create([
            'email' => $email,
            'password' => Hash::make('oldpassword'),
            'role' => 'customer',
            'country' => 'NG',
            'phone_number' => '08000000000',
            'full_name' => 'Test User',
            'email_verified_at' => now(),
        ]);
    }

    public function test_it_sends_password_reset_otp_to_email(): void
    {
        Mail::fake();

        $user = $this->makeUser('jane@example.com');

        $this->postJson('/v2/auth/forgot-password', [
            'email' => 'jane@example.com',
        ])->assertOk()
            ->assertJson([
                'message' => 'Password reset code sent successfully',
            ]);

        $this->assertDatabaseHas('otp_verifications', [
            'user_id' => $user->id,
            'type' => 'password_reset',
        ]);

        Mail::assertSent(OtpCode::class, function (OtpCode $mailable) use ($user) {
            return $mailable->user->is($user) && $mailable->otp->type === 'password_reset';
        });
    }

    public function test_it_resets_password_with_valid_otp(): void
    {
        Mail::fake();

        $user = $this->makeUser('john@example.com');

        $otp = OtpVerification::create([
            'user_id' => $user->id,
            'otp' => '123456',
            'type' => 'password_reset',
        ]);

        $this->postJson('/v2/auth/forgot-password/reset', [
            'otp' => '123456',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertOk()
            ->assertJson([
                'message' => 'Password reset successfully',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword', $user->password));

        $this->assertDatabaseMissing('otp_verifications', [
            'id' => $otp->id,
        ]);

        Mail::assertSent(PasswordChangedMail::class);
    }

    public function test_it_rejects_invalid_otp(): void
    {
        $user = $this->makeUser('mark@example.com');

        OtpVerification::create([
            'user_id' => $user->id,
            'otp' => '654321',
            'type' => 'password_reset',
        ]);

        $this->postJson('/v2/auth/forgot-password/reset', [
            'otp' => '000000',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ])->assertStatus(400)
            ->assertJson([
                'message' => 'Invalid OTP',
            ]);
    }
}
