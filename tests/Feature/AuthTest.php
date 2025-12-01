<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    // Resets the database after each test, ensuring clean state
    use RefreshDatabase;
    use WithFaker;

    /** @var string The user's default password for testing */
    protected $testPassword = 'password123';

    /**
     * Set up a test user before running tests.
     * This method runs before every test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a user in the test database for login attempts
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt($this->testPassword),
        ]);
    }

    /**
     * Test a successful user login.
     * @return void
     */
    public function test_user_can_login_successfully()
    {
        $response = $this->postJson('/api/user/login', [
            'email' => 'admin@example.com',
            'password' => $this->testPassword,
        ]);

        $response->assertStatus(200) // Expect HTTP 200 OK
            ->assertJsonStructure([ // Check for common successful login response structure
                'token',
                'user' => ['id', 'email'],
            ]);
    }

    /**
     * Test login failure with invalid password.
     * @return void
     */
    public function test_login_fails_with_invalid_password()
    {
        $response = $this->postJson('/api/user/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'failed', // Add the status key
                // Use the exact message returned by your application:
                'message' => 'invalid credentials (email or password).',
            ]);
    }

    /**
     * Test login failure with missing required fields (validation error).
     * @return void
     */
    public function test_login_fails_with_missing_fields()
    {
        $response = $this->postJson('/api/user/login', [
            'email' => '',
            'password' => $this->testPassword,
        ]);

        $response->assertStatus(422); // Assert the status code first

        // Use a custom assertion to check for the field error in your custom structure
        // This assumes your custom validation error includes a 'message' field indicating the error.
        $response->assertJsonStructure([
            'message', // General error message
            // If your structure uses a custom field for errors, adjust here, 
            // e.g., 'validation_errors' => ['email']
        ]);
    }
}
