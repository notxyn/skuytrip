<?php

namespace Tests\Helpers;

use Mockery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Helper class untuk mempermudah testing dan mocking
 */
class TestHelper
{
    /**
     * Create a fake image file for testing
     */
    public static function createFakeImage(string $name = 'test-image.jpg', int $width = 640, int $height = 480): UploadedFile
    {
        return UploadedFile::fake()->image($name, $width, $height);
    }

    /**
     * Create multiple fake images
     */
    public static function createFakeImages(int $count = 3): array
    {
        $images = [];
        for ($i = 1; $i <= $count; $i++) {
            $images[] = self::createFakeImage("test-image-{$i}.jpg");
        }
        return $images;
    }

    /**
     * Setup storage fake with directory structure
     */
    public static function setupStorageFake(): void
    {
        Storage::fake('public');
        Storage::disk('public')->makeDirectory('attractions');
        Storage::disk('public')->makeDirectory('payments');
        Storage::disk('public')->makeDirectory('temp');
    }

    /**
     * Generate test attraction data
     */
    public static function generateAttractionData(array $overrides = []): array
    {
        $faker = \Faker\Factory::create();
        
        $defaultData = [
            'slug' => $faker->slug(),
            'name' => $faker->sentence(3, false),
            'loc' => $faker->city(),
            'desc' => $faker->paragraph(3),
            'rate' => $faker->randomFloat(1, 1, 5),
            'price' => $faker->numberBetween(50000, 500000),
            'tags' => implode(',', $faker->randomElements([
                'tourism', 'adventure', 'family', 'outdoor', 'cultural'
            ], 3)),
        ];

        return array_merge($defaultData, $overrides);
    }

    /**
     * Generate test booking data
     */
    public static function generateBookingData(array $overrides = []): array
    {
        $faker = \Faker\Factory::create();
        
        $defaultData = [
            'user_id' => $faker->numberBetween(1, 10),
            'attraction_id' => $faker->numberBetween(1, 20),
            'name' => $faker->name(),
            'email' => $faker->email(),
            'phone' => $faker->phoneNumber(),
            'date' => $faker->date(),
            'quantity' => $faker->numberBetween(1, 5),
            'total' => $faker->numberBetween(100000, 1000000),
            'payment_method' => $faker->randomElement(['visa', 'mastercard', 'paypal', 'bank_transfer', 'cash']),
            'status' => $faker->randomElement(['pending', 'paid', 'cancelled', 'refunded']),
        ];

        return array_merge($defaultData, $overrides);
    }

    /**
     * Generate test user data
     */
    public static function generateUserData(array $overrides = []): array
    {
        $faker = \Faker\Factory::create();
        
        $defaultData = [
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => bcrypt('password'),
            'is_admin' => $faker->boolean(20), // 20% chance of being admin
        ];

        return array_merge($defaultData, $overrides);
    }

    /**
     * Create multiple attraction data
     */
    public static function generateMultipleAttractionData(int $count = 3, array $overrides = []): array
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = self::generateAttractionData($overrides);
        }
        return $data;
    }

    /**
     * Create multiple booking data
     */
    public static function generateMultipleBookingData(int $count = 3, array $overrides = []): array
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = self::generateBookingData($overrides);
        }
        return $data;
    }

    /**
     * Create multiple user data
     */
    public static function generateMultipleUserData(int $count = 3, array $overrides = []): array
    {
        $data = [];
        for ($i = 0; $i < $count; $i++) {
            $data[] = self::generateUserData($overrides);
        }
        return $data;
    }
}

/**
 * Mock helper untuk Filament components
 */
class FilamentMockHelper
{
    /**
     * Create a basic Form mock
     */
    public static function createFormMock(): \Mockery\MockInterface
    {
        $form = Mockery::mock(\Filament\Forms\Form::class);
        $form->shouldReceive('schema')
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        
        return $form;
    }

    /**
     * Create a basic Table mock
     */
    public static function createTableMock(): \Mockery\MockInterface
    {
        $table = Mockery::mock(\Filament\Tables\Table::class);
        
        $table->shouldReceive('columns')
            ->with(Mockery::type('array'))
            ->andReturnSelf();
            
        $table->shouldReceive('filters')
            ->with(Mockery::type('array'))
            ->andReturnSelf();
            
        $table->shouldReceive('actions')
            ->with(Mockery::type('array'))
            ->andReturnSelf();
            
        $table->shouldReceive('bulkActions')
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        
        return $table;
    }

    /**
     * Create a mock for capturing form schema
     */
    public static function createFormSchemaCaptorMock(&$capturedSchema): \Mockery\MockInterface
    {
        $form = Mockery::mock(\Filament\Forms\Form::class);
        $form->shouldReceive('schema')
            ->once()
            ->with(Mockery::on(function ($schema) use (&$capturedSchema) {
                $capturedSchema = $schema;
                return is_array($schema);
            }))
            ->andReturnSelf();
        
        return $form;
    }

    /**
     * Create a mock for capturing table columns
     */
    public static function createTableColumnsCaptorMock(&$capturedColumns): \Mockery\MockInterface
    {
        $table = Mockery::mock(\Filament\Tables\Table::class);
        
        $table->shouldReceive('columns')
            ->once()
            ->with(Mockery::on(function ($columns) use (&$capturedColumns) {
                $capturedColumns = $columns;
                return is_array($columns);
            }))
            ->andReturnSelf();
            
        $table->shouldReceive('filters')->andReturnSelf();
        $table->shouldReceive('actions')->andReturnSelf();
        $table->shouldReceive('bulkActions')->andReturnSelf();
        
        return $table;
    }
}

/**
 * Assertion helper untuk testing
 */
class AssertionHelper
{
    /**
     * Assert that form schema contains specific field types
     */
    public static function assertFormSchemaContainsFields(array $schema, array $expectedFields): void
    {
        $actualFields = [];
        foreach ($schema as $field) {
            if (method_exists($field, 'getName')) {
                $actualFields[] = $field->getName();
            }
        }
        
        foreach ($expectedFields as $expectedField) {
            \PHPUnit\Framework\Assert::assertContains(
                $expectedField,
                $actualFields,
                "Form schema should contain field: {$expectedField}"
            );
        }
    }

    /**
     * Assert that table columns contain specific column names
     */
    public static function assertTableColumnsContain(array $columns, array $expectedColumns): void
    {
        $actualColumns = [];
        foreach ($columns as $column) {
            if (method_exists($column, 'getName')) {
                $actualColumns[] = $column->getName();
            }
        }
        
        foreach ($expectedColumns as $expectedColumn) {
            \PHPUnit\Framework\Assert::assertContains(
                $expectedColumn,
                $actualColumns,
                "Table columns should contain: {$expectedColumn}"
            );
        }
    }

    /**
     * Assert that array contains specific values
     */
    public static function assertArrayContainsValues(array $array, array $expectedValues): void
    {
        foreach ($expectedValues as $value) {
            \PHPUnit\Framework\Assert::assertContains(
                $value,
                $array,
                "Array should contain value: {$value}"
            );
        }
    }
}

/**
 * Database testing helper
 */
class DatabaseTestHelper
{
    /**
     * Create test attractions in database
     */
    public static function createTestAttractions(int $count = 5): \Illuminate\Support\Collection
    {
        return \App\Models\Attraction::factory()->count($count)->create();
    }

    /**
     * Create test attraction with specific data
     */
    public static function createTestAttraction(array $data = []): \App\Models\Attraction
    {
        return \App\Models\Attraction::factory()->create($data);
    }

    /**
     * Create test bookings in database
     */
    public static function createTestBookings(int $count = 5): \Illuminate\Support\Collection
    {
        return \App\Models\Booking::factory()->count($count)->create();
    }

    /**
     * Create test booking with specific data
     */
    public static function createTestBooking(array $data = []): \App\Models\Booking
    {
        return \App\Models\Booking::factory()->create($data);
    }

    /**
     * Create test users in database
     */
    public static function createTestUsers(int $count = 5): \Illuminate\Support\Collection
    {
        return \App\Models\User::factory()->count($count)->create();
    }

    /**
     * Create test user with specific data
     */
    public static function createTestUser(array $data = []): \App\Models\User
    {
        return \App\Models\User::factory()->create($data);
    }

    /**
     * Assert database has attraction with specific data
     */
    public static function assertDatabaseHasAttraction(array $data): void
    {
        \Illuminate\Support\Facades\DB::table('attractions')->where($data)->exists() 
            ? \PHPUnit\Framework\Assert::assertTrue(true) 
            : \PHPUnit\Framework\Assert::fail('Database does not contain the expected attraction data');
    }

    /**
     * Assert database doesn't have attraction with specific data
     */
    public static function assertDatabaseMissingAttraction(array $data): void
    {
        \Illuminate\Support\Facades\DB::table('attractions')->where($data)->exists() 
            ? \PHPUnit\Framework\Assert::fail('Database contains unexpected attraction data') 
            : \PHPUnit\Framework\Assert::assertTrue(true);
    }

    /**
     * Assert database has booking with specific data
     */
    public static function assertDatabaseHasBooking(array $data): void
    {
        \Illuminate\Support\Facades\DB::table('bookings')->where($data)->exists() 
            ? \PHPUnit\Framework\Assert::assertTrue(true) 
            : \PHPUnit\Framework\Assert::fail('Database does not contain the expected booking data');
    }

    /**
     * Assert database doesn't have booking with specific data
     */
    public static function assertDatabaseMissingBooking(array $data): void
    {
        \Illuminate\Support\Facades\DB::table('bookings')->where($data)->exists() 
            ? \PHPUnit\Framework\Assert::fail('Database contains unexpected booking data') 
            : \PHPUnit\Framework\Assert::assertTrue(true);
    }
} 