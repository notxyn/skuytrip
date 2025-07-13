<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProfileUpdateRequestTest extends TestCase
{
    use DatabaseMigrations;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertTrue($isValid);
        $this->assertEmpty($validator->errors());
    }

    /** @test */
    public function it_fails_validation_when_name_is_missing()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'email' => 'jane@example.com',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function it_fails_validation_when_name_is_empty()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => '',
            'email' => 'jane@example.com',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function it_fails_validation_when_name_is_not_string()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 123,
            'email' => 'jane@example.com',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function it_fails_validation_when_name_exceeds_max_length()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => str_repeat('a', 256),
            'email' => 'jane@example.com',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('name'));
    }

    /** @test */
    public function it_fails_validation_when_email_is_missing()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('email'));
    }

    /** @test */
    public function it_fails_validation_when_email_is_empty()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
            'email' => '',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('email'));
    }

    /** @test */
    public function it_fails_validation_when_email_is_not_string()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
            'email' => 123,
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('email'));
    }

    /** @test */
    public function it_fails_validation_when_email_has_invalid_format()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
            'email' => 'invalid-email',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('email'));
    }

    /** @test */
    public function it_fails_validation_when_email_exceeds_max_length()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
            'email' => str_repeat('a', 250) . '@example.com',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('email'));
    }

    /** @test */
    public function it_fails_validation_when_email_already_exists_for_other_user()
    {
        // Arrange
        User::factory()->create(['email' => 'existing@example.com']);
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
            'email' => 'existing@example.com',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertFalse($isValid);
        $this->assertTrue($validator->errors()->has('email'));
    }

    /** @test */
    public function it_passes_validation_when_email_belongs_to_same_user()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
            'email' => 'john@example.com', // Same email as current user
        ];

        // Mock the user method to return our test user
        $request->setUserResolver(function () {
            return $this->user;
        });

        // Act
        $validator = Validator::make($data, $request->rules());
        $isValid = $validator->passes();

        // Assert
        $this->assertTrue($isValid);
        $this->assertEmpty($validator->errors());
    }

    /** @test */
    public function it_converts_email_to_lowercase()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $data = [
            'name' => 'Jane Smith',
            'email' => 'JANE@EXAMPLE.COM',
        ];

        // Act
        $validator = Validator::make($data, $request->rules());
        $validator->validate();

        // Assert
        $this->assertEquals('jane@example.com', $validator->getData()['email']);
    }

    /** @test */
    public function it_validates_all_required_rules_are_present()
    {
        // Arrange
        $request = new ProfileUpdateRequest();
        $rules = $request->rules();

        // Assert
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
        
        $this->assertContains('required', $rules['name']);
        $this->assertContains('string', $rules['name']);
        $this->assertContains('max:255', $rules['name']);
        
        $this->assertContains('required', $rules['email']);
        $this->assertContains('string', $rules['email']);
        $this->assertContains('lowercase', $rules['email']);
        $this->assertContains('email', $rules['email']);
        $this->assertContains('max:255', $rules['email']);
    }
} 