<?php

namespace Tests\Unit\Views;

use Tests\TestCase;
use App\Models\Attraction;
use App\Models\User;
use Tests\Helpers\TestHelper;
use Mockery;
use Illuminate\Support\Facades\View;

class CheckoutViewTest extends TestCase
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
    public function it_renders_checkout_page_with_booking_details()
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
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('+6281234567890');
        $response->assertSee('2024-01-15');
        $response->assertSee('2 tickets');
    }

    /** @test */
    public function it_renders_order_summary_correctly()
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
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Beach Resort Bali');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Bali, Indonesia');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('beach-resort.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(250000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=3');

        $response->assertStatus(200);
        $response->assertSee('Beach Resort Bali');
        $response->assertSee('Bali, Indonesia');
        $response->assertSee('Rp250.000'); // Price per ticket
        $response->assertSee('× 3'); // Quantity
        $response->assertSee('Rp750.000'); // Total
    }

    /** @test */
    public function it_renders_payment_methods()
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
        $response->assertSee('Visa');
        $response->assertSee('Mastercard');
        $response->assertSee('PayPal');
        $response->assertSee('Upload Payment Proof');
        $response->assertSee('Pay Now');
    }

    /** @test */
    public function it_renders_success_page_when_booking_completed()
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
        $response->assertSee('Success Checkout');
        $response->assertSee('Thank you for supporting us');
        $response->assertSee('Check My Transactions');
        $response->assertDontSee('Booking');
        $response->assertDontSee('Order Summary');
        $response->assertDontSee('Payment Method');
    }

    /** @test */
    public function it_renders_recommended_attractions_on_success()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        // Mock recommended attractions
        $recommendedAttraction = Mockery::mock(Attraction::class);
        $recommendedAttraction->shouldReceive('getAttribute')->with('name')->andReturn('Mountain Resort');
        $recommendedAttraction->shouldReceive('getAttribute')->with('loc')->andReturn('Bandung, Indonesia');
        $recommendedAttraction->shouldReceive('getAttribute')->with('img')->andReturn('mountain-resort.jpg');
        $recommendedAttraction->shouldReceive('getAttribute')->with('slug')->andReturn('mountain-resort');
        $recommendedAttraction->shouldReceive('getAttribute')->with('tags')->andReturn(['adventure', 'outdoor']);
        
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
        $response->assertSee('Mountain Resort');
        $response->assertSee('Bandung, Indonesia');
        $response->assertSee('adventure');
        $response->assertSee('outdoor');
        $response->assertSee('View');
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
        $response->assertSee('No+Image'); // Placeholder image
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
        $response->assertSee('https://example.com/image.jpg');
    }

    /** @test */
    public function it_renders_form_with_correct_action()
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
        $response->assertSee('action="/checkout/test-attraction"');
        $response->assertSee('method="POST"');
        $response->assertSee('enctype="multipart/form-data"');
    }

    /** @test */
    public function it_renders_hidden_form_fields()
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
        $response->assertSee('name="name" value="John Doe"');
        $response->assertSee('name="email" value="john@example.com"');
        $response->assertSee('name="phone" value="+6281234567890"');
        $response->assertSee('name="date" value="2024-01-15"');
        $response->assertSee('name="quantity" value="2"');
    }

    /** @test */
    public function it_renders_payment_method_radio_buttons()
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
        $response->assertSee('name="payment" value="visa"');
        $response->assertSee('name="payment" value="mastercard"');
        $response->assertSee('name="payment" value="paypal"');
        $response->assertSee('required');
    }

    /** @test */
    public function it_renders_file_upload_field()
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
        $response->assertSee('name="payment_proof"');
        $response->assertSee('accept="image/*,application/pdf"');
        $response->assertSee('required');
    }

    /** @test */
    public function it_renders_error_messages()
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

        $response = $this->withSession(['errors' => ['payment_proof' => ['The payment proof field is required.']]])
            ->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertSee('The payment proof field is required.');
    }

    /** @test */
    public function it_renders_csrf_token()
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
        $response->assertSee('@csrf');
    }

    /** @test */
    public function it_renders_price_formatting_correctly()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        $this->actingAs($user);

        // Mock attraction with high price
        $attraction = Mockery::mock(Attraction::class);
        $attraction->shouldReceive('getAttribute')->with('slug')->andReturn('test-attraction');
        $attraction->shouldReceive('getAttribute')->with('name')->andReturn('Luxury Resort');
        $attraction->shouldReceive('getAttribute')->with('loc')->andReturn('Bali, Indonesia');
        $attraction->shouldReceive('getAttribute')->with('img')->andReturn('luxury-resort.jpg');
        $attraction->shouldReceive('getAttribute')->with('price')->andReturn(1500000);

        Attraction::shouldReceive('where')
            ->with('slug', 'test-attraction')
            ->andReturnSelf();
        Attraction::shouldReceive('firstOrFail')
            ->andReturn($attraction);

        $response = $this->get('/checkout/test-attraction?name=John%20Doe&email=john@example.com&phone=+6281234567890&date=2024-01-15&quantity=2');

        $response->assertStatus(200);
        $response->assertSee('Rp1.500.000'); // Price per ticket
        $response->assertSee('Rp3.000.000'); // Total
    }

    /** @test */
    public function it_renders_attraction_tags_correctly()
    {
        // Mock authenticated user
        $user = Mockery::mock(User::class);
        $user->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');
        
        // Mock recommended attractions with tags
        $recommendedAttraction = Mockery::mock(Attraction::class);
        $recommendedAttraction->shouldReceive('getAttribute')->with('name')->andReturn('Adventure Park');
        $recommendedAttraction->shouldReceive('getAttribute')->with('loc')->andReturn('Jakarta, Indonesia');
        $recommendedAttraction->shouldReceive('getAttribute')->with('img')->andReturn('adventure-park.jpg');
        $recommendedAttraction->shouldReceive('getAttribute')->with('slug')->andReturn('adventure-park');
        $recommendedAttraction->shouldReceive('getAttribute')->with('tags')->andReturn(['adventure', 'outdoor', 'family']);
        
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
        $response->assertSee('adventure');
        $response->assertSee('outdoor');
        $response->assertSee('family');
    }
} 