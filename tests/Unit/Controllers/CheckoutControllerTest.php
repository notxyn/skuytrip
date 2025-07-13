<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Models\Attraction;
use App\Models\Booking;
use App\Models\User;
use Tests\Helpers\TestHelper;
use Mockery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class CheckoutControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestHelper::setupStorageFake();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_validates_booking_data_before_checkout()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Test missing phone
        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&date=2024-01-15&quantity=2');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Please fill out the booking form first.');

        // Test missing date
        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&quantity=2');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Please fill out the booking form first.');

        // Test missing quantity
        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Please fill out the booking form first.');
    }

    /** @test */
    public function it_calculates_price_correctly_from_string()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction with price string
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('test-image.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn('Rp 250.000');

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=3');

        $response->assertStatus(200);
        $response->assertViewHas('order', function ($order) {
            return $order['price'] === 250000 && $order['total'] === 750000;
        });
    }

    /** @test */
    public function it_handles_price_with_commas_and_dots()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction with price containing commas and dots
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('test-image.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn('1,500.00');

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertViewHas('order', function ($order) {
            return $order['price'] === 150000 && $order['total'] === 300000;
        });
    }

    /** @test */
    public function it_validates_payment_proof_file_type()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'payment' => 'visa',
            'payment_proof' => $invalidFile
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
    }

    /** @test */
    public function it_validates_payment_proof_file_size()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Test large file
        $largeFile = UploadedFile::fake()->image('large-image.jpg')->size(3000); // 3MB

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'payment' => 'visa',
            'payment_proof' => $largeFile
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
    }

    /** @test */
    public function it_accepts_valid_payment_proof_files()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Mock Booking creation
        $booking = Mockery::mock(Booking::class);
        $booking->shouldReceive('getAttribute')->with('id')->andReturn(1);

        Booking::shouldReceive('create')
            ->andReturn($booking);

        // Test valid image file
        $validImage = TestHelper::createFakeImage('payment-proof.jpg');

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'payment' => 'visa',
            'payment_proof' => $validImage
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_accepts_pdf_payment_proof()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Mock Booking creation
        $booking = Mockery::mock(Booking::class);
        $booking->shouldReceive('getAttribute')->with('id')->andReturn(1);

        Booking::shouldReceive('create')
            ->andReturn($booking);

        // Test PDF file
        $pdfFile = UploadedFile::fake()->create('payment-proof.pdf', 100, 'application/pdf');

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'payment' => 'visa',
            'payment_proof' => $pdfFile
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_creates_booking_with_correct_data()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(150000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Mock file upload
        $paymentProof = TestHelper::createFakeImage('payment-proof.jpg');

        // Mock Booking creation
        $booking = Mockery::mock(Booking::class);
        $booking->shouldReceive('getAttribute')->with('id')->andReturn(1);

        Booking::shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['user_id'] === 1 &&
                       $data['attraction_id'] === 1 &&
                       $data['name'] === 'John Doe' &&
                       $data['email'] === 'john@example.com' &&
                       $data['phone'] === '+6281234567890' &&
                       $data['date'] === '2024-01-15' &&
                       $data['quantity'] === 3 &&
                       $data['total'] === 450000 &&
                       $data['payment_method'] === 'mastercard' &&
                       $data['status'] === 'pending' &&
                       !empty($data['payment_proof']);
            }))
            ->andReturn($booking);

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 3,
            'payment' => 'mastercard',
            'payment_proof' => $paymentProof
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_handles_default_payment_method()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Mock file upload
        $paymentProof = TestHelper::createFakeImage('payment-proof.jpg');

        // Mock Booking creation
        $booking = Mockery::mock(Booking::class);
        $booking->shouldReceive('getAttribute')->with('id')->andReturn(1);

        Booking::shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['payment_method'] === 'visa'; // Default payment method
            }))
            ->andReturn($booking);

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'payment_proof' => $paymentProof
            // No payment method specified
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_handles_zero_or_negative_quantity()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Mock file upload
        $paymentProof = TestHelper::createFakeImage('payment-proof.jpg');

        // Mock Booking creation
        $booking = Mockery::mock(Booking::class);
        $booking->shouldReceive('getAttribute')->with('id')->andReturn(1);

        Booking::shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['quantity'] === 1 && $data['total'] === 100000; // Should default to 1
            }))
            ->andReturn($booking);

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 0, // Zero quantity
            'payment' => 'visa',
            'payment_proof' => $paymentProof
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_redirects_with_booking_data_after_success()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Mock file upload
        $paymentProof = TestHelper::createFakeImage('payment-proof.jpg');

        // Mock Booking creation
        $booking = Mockery::mock(Booking::class);
        $booking->shouldReceive('getAttribute')->with('id')->andReturn(1);

        Booking::shouldReceive('create')
            ->andReturn($booking);

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'payment' => 'visa',
            'payment_proof' => $paymentProof
        ]);

        $response->assertRedirect('/checkout/test-attraction?name=John%20Doe&email=john%40example.com&phone=%2B6281234567890&date=2024-01-15&quantity=2');
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_handles_missing_attraction()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        Attraction::shouldReceive('where')
            ->with('slug', 'nonexistent-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andThrow(new \Illuminate\Database\Eloquent\ModelNotFoundException());

        $response = $this->get('/checkout/nonexistent-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_handles_file_upload_failure()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        // Mock file upload that fails
        $paymentProof = TestHelper::createFakeImage('payment-proof.jpg');
        
        // Mock storage failure
        Storage::shouldReceive('disk')
            ->with('public')
            ->andReturnSelf();
        Storage::shouldReceive('putFileAs')
            ->andReturn(false);

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'payment' => 'visa',
            'payment_proof' => $paymentProof
        ]);

        $response->assertSessionHasErrors();
    }
} 