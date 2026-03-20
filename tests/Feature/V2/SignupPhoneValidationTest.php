<?php

namespace Tests\Feature\V2;

use App\Events\UserRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SignupPhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_accepts_a_valid_phone_number_for_the_selected_country(): void
    {
        Event::fake([UserRegistered::class]);

        $this->postJson('/v2/auth/signup', [
            'full_name' => 'Jane Doe',
            'business_name' => 'Acme Events',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'country' => 'ng',
            'phone_number' => '+2348031234567',
        ])->assertStatus(201)
            ->assertJson([
                'message' => 'Successfully registered. Verify your email to complete registration',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'country' => 'NG',
            'phone_number' => '+2348031234567',
        ]);

        Event::assertDispatched(UserRegistered::class);
    }

    #[Test]
    public function it_rejects_a_phone_number_that_does_not_match_the_country(): void
    {
        Event::fake([UserRegistered::class]);

        $this->postJson('/v2/auth/signup', [
            'full_name' => 'John Doe',
            'business_name' => 'Acme Events',
            'email' => 'john@example.com',
            'password' => 'password123',
            'country' => 'US',
            'phone_number' => '08031234567',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);

        Event::assertNotDispatched(UserRegistered::class);
    }
}

