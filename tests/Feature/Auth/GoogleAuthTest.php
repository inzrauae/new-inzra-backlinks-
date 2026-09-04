<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_redirect_route_sends_the_user_to_google(): void
    {
        Socialite::fake('google', SocialiteUser::fake());

        $response = $this->get('/auth/google');

        $response->assertRedirect();
    }

    public function test_a_new_google_user_is_created_and_logged_in(): void
    {
        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-123',
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'avatar' => 'https://example.com/ada.jpg',
        ]));

        $response = $this->get('/auth/google/callback');

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'ada@example.com',
            'google_id' => 'google-123',
            'role' => UserRole::Customer->value,
        ]);

        $user = User::where('email', 'ada@example.com')->first();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->password);
    }

    public function test_an_existing_account_with_a_matching_email_is_linked_instead_of_duplicated(): void
    {
        $existing = User::factory()->create(['email' => 'linkme@example.com']);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-456',
            'email' => 'linkme@example.com',
            'name' => 'Existing User',
        ]));

        $this->get('/auth/google/callback');

        $this->assertAuthenticatedAs($existing->fresh());
        $this->assertSame('google-456', $existing->fresh()->google_id);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_a_returning_google_user_logs_in_by_google_id(): void
    {
        $existing = User::factory()->create([
            'google_id' => 'google-789',
            'email' => 'returning@example.com',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-789',
            'email' => 'returning@example.com',
        ]));

        $this->get('/auth/google/callback');

        $this->assertAuthenticatedAs($existing);
        $this->assertDatabaseCount('users', 1);
    }
}
