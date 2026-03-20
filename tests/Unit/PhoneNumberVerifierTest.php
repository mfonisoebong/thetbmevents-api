<?php

namespace Tests\Unit;

use App\Services\PhoneNumberVerifier;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PhoneNumberVerifierTest extends TestCase
{
    #[Test]
    public function it_validates_supported_country_numbers(): void
    {
        $this->assertTrue(PhoneNumberVerifier::verifyPhoneNumber('NG', '+2348031234567'));
        $this->assertTrue(PhoneNumberVerifier::verifyPhoneNumber('GB', '+442083661177'));
        $this->assertTrue(PhoneNumberVerifier::verifyPhoneNumber('US', '+14155552671'));
    }

    #[Test]
    public function it_rejects_invalid_or_unsupported_country_numbers(): void
    {
        $this->assertFalse(PhoneNumberVerifier::verifyPhoneNumber('US', '08031234567'));
        $this->assertFalse(PhoneNumberVerifier::verifyPhoneNumber('FR', '+33123456789'));
        $this->assertFalse(PhoneNumberVerifier::verifyPhoneNumber('NG', ''));
    }

    #[Test]
    public function it_supports_country_specific_shortcuts(): void
    {
        $this->assertTrue(PhoneNumberVerifier::isValidNigerianNumber('+2348031234567'));
        $this->assertTrue(PhoneNumberVerifier::isValidUkNumber('+442083661177'));
        $this->assertTrue(PhoneNumberVerifier::isValidUsNumber('+14155552671'));
    }
}
