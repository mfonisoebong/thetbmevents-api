<?php

namespace Tests\Feature;

use App\Mail\VerifyEmailViaLink;
use App\Models\User;
use Tests\TestCase;

class VerifyEmailViaLinkMailableTest extends TestCase
{
    public function test_verify_email_link_contains_client_url_and_hash(): void
    {
        config(['app.client_url' => 'https://client.example']);

        $user = new User();
        $user->full_name = 'Test User';
        $user->email = 'test@example.com';

        $hash = 'abc123HASH';

        $mailable = new VerifyEmailViaLink($user, $hash);

        $rendered = $mailable->render();

        $this->assertStringContainsString('https://client.example/verify-email/' . rawurlencode($hash), $rendered);
        $this->assertStringContainsString('Hi Test User', $rendered);
    }
}

