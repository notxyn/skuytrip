<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\ProfileController;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\RedirectResponse;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use DatabaseMigrations;

    private ProfileController $controller;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ProfileController();
        
        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function it_can_update_user_profile_with_valid_data()
    {
        // Arrange
        $data = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        // Assert
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');

        // Verify the user was updated in the database
        $this->user->refresh();
        $this->assertEquals('Jane Smith', $this->user->name);
        $this->assertEquals('jane@example.com', $this->user->email);
    }

    /** @test */
    public function it_resets_email_verification_when_email_is_changed()
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'newemail@example.com', // Different email
        ];

        // Act
        $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        // Assert
        $this->user->refresh();
        $this->assertNull($this->user->email_verified_at);
    }

    /** @test */
    public function it_does_not_reset_email_verification_when_email_is_unchanged()
    {
        // Arrange
        $originalVerifiedAt = $this->user->email_verified_at;
        $data = [
            'name' => 'John Doe Updated',
            'email' => 'john@example.com', // Same email
        ];

        // Act
        $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        // Assert
        $this->user->refresh();
        $this->assertEquals($originalVerifiedAt, $this->user->email_verified_at);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        // Arrange
        $data = [
            'name' => '', // Empty name
            'email' => '', // Empty email
        ];

        // Act & Assert
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /** @test */
    public function it_validates_email_format()
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email', // Invalid email format
        ];

        // Act & Assert
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_email_uniqueness()
    {
        // Arrange - Create another user with a different email
        $otherUser = User::factory()->create([
            'email' => 'other@example.com',
        ]);

        $data = [
            'name' => 'John Doe',
            'email' => 'other@example.com', // Email already exists
        ];

        // Act & Assert
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_allows_user_to_keep_their_own_email()
    {
        // Arrange
        $data = [
            'name' => 'John Doe Updated',
            'email' => 'john@example.com', // Same email as current user
        ];

        // Act
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        // Assert
        $response->assertRedirect(route('profile.edit'));
        $this->user->refresh();
        $this->assertEquals('John Doe Updated', $this->user->name);
        $this->assertEquals('john@example.com', $this->user->email);
    }

    /** @test */
    public function it_converts_email_to_lowercase()
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => 'JOHN@EXAMPLE.COM', // Uppercase email
        ];

        // Act
        $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        // Assert
        $this->user->refresh();
        $this->assertEquals('john@example.com', $this->user->email);
    }

    /** @test */
    public function it_validates_name_max_length()
    {
        // Arrange
        $data = [
            'name' => str_repeat('a', 256), // Exceeds 255 character limit
            'email' => 'john@example.com',
        ];

        // Act & Assert
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function it_validates_email_max_length()
    {
        // Arrange
        $data = [
            'name' => 'John Doe',
            'email' => str_repeat('a', 250) . '@example.com', // Exceeds 255 character limit
        ];

        // Act & Assert
        $response = $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_preserves_other_user_attributes_when_updating()
    {
        // Arrange
        $originalPassword = $this->user->password;
        $originalIsAdmin = $this->user->is_admin;
        
        $data = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ];

        // Act
        $this->actingAs($this->user)
            ->patch(route('profile.update'), $data);

        // Assert
        $this->user->refresh();
        $this->assertEquals($originalPassword, $this->user->password);
        $this->assertEquals($originalIsAdmin, $this->user->is_admin);
    }
} 