<?php

namespace Tests\Unit\Features;

use Tests\TestCase;
use App\Models\Attraction;
use App\Models\Booking;
use App\Models\User;
use Tests\Helpers\TestHelper;
use Mockery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CheckoutTest extends TestCase
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
    public function it_displays_checkout_page_with_valid_data()
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
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('test-image.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertViewIs('checkout');
        $response->assertViewHas('booking');
        $response->assertViewHas('order');
        $response->assertViewHas('attraction');
    }

    /** @test */
    public function it_redirects_when_booking_data_is_missing()
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

        $response = $this->get('/checkout/test-attraction');

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Please fill out the booking form first.');
    }

    /** @test */
    public function it_calculates_order_total_correctly()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction with price
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('test-image.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(150000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=3');

        $response->assertStatus(200);
        
        // Check that total is calculated correctly (150000 * 3 = 450000)
        $response->assertViewHas('order', function ($order) {
            return $order['total'] === 450000;
        });
    }

    /** @test */
    public function it_processes_payment_successfully()
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
                return $data['user_id'] === 1 &&
                       $data['attraction_id'] === 1 &&
                       $data['name'] === 'John Doe' &&
                       $data['email'] === 'john@example.com' &&
                       $data['phone'] === '+6281234567890' &&
                       $data['date'] === '2024-01-15' &&
                       $data['quantity'] === 2 &&
                       $data['total'] === 200000 &&
                       $data['payment_method'] === 'visa' &&
                       $data['status'] === 'pending' &&
                       !empty($data['payment_proof']);
            }))
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

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_validates_payment_proof_upload()
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

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 2,
            'payment' => 'visa'
            // Missing payment_proof
        ]);

        $response->assertSessionHasErrors(['payment_proof']);
    }

    /** @test */
    public function it_handles_invalid_file_type()
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

        // Create invalid file (text file instead of image)
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
    public function it_handles_large_file_upload()
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

        // Create large file (exceeds 2MB limit)
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
    public function it_handles_different_payment_methods()
    {
        $paymentMethods = ['visa', 'mastercard', 'paypal'];

        foreach ($paymentMethods as $method) {
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
                ->with(Mockery::on(function ($data) use ($method) {
                    return $data['payment_method'] === $method;
                }))
                ->andReturn($booking);

            $response = $this->post('/checkout/test-attraction', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+6281234567890',
                'date' => '2024-01-15',
                'quantity' => 2,
                'payment' => $method,
                'payment_proof' => $paymentProof
            ]);

            $response->assertRedirect();
            $response->assertSessionHas('success', 'Booking successful!');
        }
    }

    /** @test */
    public function it_handles_zero_quantity()
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

        // Mock Booking creation - should default to quantity 1
        $booking = Mockery::mock(Booking::class);
        $booking->shouldReceive('getAttribute')->with('id')->andReturn(1);

        Booking::shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['quantity'] === 1 && $data['total'] === 100000;
            }))
            ->andReturn($booking);

        $response = $this->post('/checkout/test-attraction', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+6281234567890',
            'date' => '2024-01-15',
            'quantity' => 0,
            'payment' => 'visa',
            'payment_proof' => $paymentProof
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_handles_attraction_without_image()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction without image
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn(null);
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertViewHas('order', function ($order) {
            return $order['img'] === null;
        });
    }

    /** @test */
    public function it_handles_external_image_urls()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction with external image URL
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('https://example.com/image.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertViewHas('order', function ($order) {
            return $order['img'] === 'https://example.com/image.jpg';
        });
    }

    /** @test */
    public function it_handles_price_with_currency_symbols()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction with price containing currency symbols
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('test-image.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn('Rp 150.000');

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertViewHas('order', function ($order) {
            return $order['price'] === 150000; // Should extract numeric value
        });
    }

    /** @test */
    public function it_handles_success_session()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        $user->shouldReceive('recommendedAttractions')
            ->andReturn(collect([]));
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('test-image.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->withSession(['success' => 'Booking successful!'])
            ->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertViewIs('checkout');
        $response->assertSee('Success Checkout');
        $response->assertSee('Thank you for supporting us');
    }

    /** @test */
    public function it_displays_recommended_attractions_on_success()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        // Mock recommended attractions
        $recommendedAttraction = Mockery::mock(Attraction::class);
        $recommendedAttraction->shouldReceive('getAttribute')->with('name')->andReturn('Recommended Attraction');
        $recommendedAttraction->shouldReceive('getAttribute')->with('loc')->andReturn('Recommended Location');
        $recommendedAttraction->shouldReceive('getAttribute')->with('img')->andReturn('recommended-image.jpg');
        $recommendedAttraction->shouldReceive('getAttribute')->with('slug')->andReturn('recommended-attraction');
        $recommendedAttraction->shouldReceive('getAttribute')->with('tags')->andReturn(['tourism', 'adventure']);
        
        $user->shouldReceive('recommendedAttractions')
            ->andReturn(collect([$recommendedAttraction]));
        
        $this->actingAs($user);

        // Mock attraction
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Test Attraction');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Test Location');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('test-image.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->withSession(['success' => 'Booking successful!'])
            ->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertSee('You might also like');
        $response->assertSee('Recommended Attraction');
    }

    /** @test */
    public function it_redirects_to_success_page_after_checkout()
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
                return $data['user_id'] === 1 &&
                       $data['attraction_id'] === 1 &&
                       $data['name'] === 'John Doe' &&
                       $data['email'] === 'john@example.com' &&
                       $data['phone'] === '+6281234567890' &&
                       $data['date'] === '2024-01-15' &&
                       $data['quantity'] === 2 &&
                       $data['total'] === 200000 &&
                       $data['payment_method'] === 'visa' &&
                       $data['status'] === 'pending' &&
                       !empty($data['payment_proof']);
            }))
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

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }

    /** @test */
    public function it_handles_unauthorized_access()
    {
        // Not authenticated
        $response = $this->get('/checkout/test-attraction');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function it_handles_nonexistent_attraction()
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

        $response = $this->get('/checkout/nonexistent-attraction');

        $response->assertStatus(404);
    }

    /** @test */
    public function user_can_complete_checkout()
    {
        // Use Mockery aliasing for static methods
        $attractionMock = Mockery::mock('alias:App\\Models\\Attraction');
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(100000);

        $attractionMock->shouldReceive('where')->with('slug', 'test-attraction')->andReturnSelf();
        $attractionMock->shouldReceive('firstOrFail')->andReturn($attraction);

        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        $this->actingAs($user);

        // Mock file upload
        $paymentProof = TestHelper::createFakeImage('payment-proof.jpg');

        // Mock Booking creation
        $booking = Mockery::mock(Booking::class);
        $booking->shouldReceive('getAttribute')->with('id')->andReturn(1);
        Mockery::mock('alias:App\\Models\\Booking')
            ->shouldReceive('create')
            ->with(Mockery::on(function ($data) {
                return $data['user_id'] === 1 &&
                       $data['attraction_id'] === 1 &&
                       $data['name'] === 'John Doe' &&
                       $data['email'] === 'john@example.com' &&
                       $data['phone'] === '+6281234567890' &&
                       $data['date'] === '2024-01-15' &&
                       $data['quantity'] === 2 &&
                       $data['total'] === 200000 &&
                       $data['payment_method'] === 'visa' &&
                       $data['status'] === 'pending' &&
                       !empty($data['payment_proof']);
            }))
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

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Booking successful!');
    }
} 